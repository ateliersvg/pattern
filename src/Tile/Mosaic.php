<?php

declare(strict_types=1);

namespace Atelier\Pattern\Tile;

use Atelier\Pattern\AbstractPattern;
use Atelier\Pattern\Internal\Guard;
use Atelier\Pattern\Internal\Num;
use Atelier\Pattern\Internal\Random;
use Atelier\Pattern\Internal\Wrap;
use Atelier\Svg\Element\ElementInterface;
use Atelier\Svg\Element\PathElement;
use Atelier\Svg\Element\Shape\CircleElement;

/**
 * A Voronoi diagram read on a grid: instead of the exact polygons, a square is
 * filled whole with the tone of the seed nearest its middle.
 *
 * Seeds are thrown into the square tile and the tile is cut into cells by cells
 * squares. The distance from a square to a seed is measured on the torus: per
 * axis, the shorter of the two ways, straight across the tile or out through
 * one edge and back in through the other. That distance is periodic, so the
 * seed holding a square on one edge is the seed holding the square facing it
 * across the seam: the territories run on, with no border case and nothing to
 * repeat.
 *
 * The tone of a square comes from the index of its seed modulo tones, not from
 * how far the seed is, so the surface reads as a few flat tones rather than a
 * continuum. The tones are spread evenly between MIN_OPACITY and MAX_OPACITY,
 * the bounds a filled Voronoi paints its cells between.
 *
 * Squares sharing a tone are drawn in one path. Two shapes painted side by side
 * leave a seam where their edges meet, and one path filled in a single pass
 * does not.
 */
final class Mosaic extends AbstractPattern
{
    /** Opacity of the palest tone. */
    public const float MIN_OPACITY = 0.15;

    /** Opacity of the deepest tone. */
    public const float MAX_OPACITY = 0.65;

    /** Ink on the rules, faint enough to read as a joint and not as a line. */
    private const float GRID_OPACITY = 0.12;

    /** Share of a square side the rule between two squares takes. */
    private const float GRID_THICKNESS = 1 / 12;

    /** Ink on the dot marking a seed, deeper than any tone so it stands out. */
    private const float SEED_OPACITY = 0.9;

    /** Radius of that dot, as a share of the tile side. */
    private const float SEED_RADIUS = 1 / 100;

    private function __construct(
        private readonly float $size,
        private readonly int $cells,
        private readonly int $sites,
        private readonly int $tones,
        private readonly int $seed,
        private readonly bool $grid,
        private readonly bool $seeds,
    ) {
    }

    /**
     * @param float $size  side of the square tile
     * @param int   $cells squares per side, so how coarse the pixelation is, at least 2
     * @param int   $sites seeds sharing the tile out between them, at least 2
     * @param int   $tones distinct opacities the squares are painted at, at least 2 and at most one per seed
     * @param int   $seed  same seed, same territories
     * @param bool  $grid  rules the squares, very faintly
     * @param bool  $seeds marks each seed with a dot
     */
    public static function create(float $size = 150.0, int $cells = 24, int $sites = 14, int $tones = 7, int $seed = 1, bool $grid = true, bool $seeds = true): self
    {
        Guard::positive($size, 'size');
        Guard::atLeast($cells, 2, 'cells');
        Guard::atLeast($sites, 2, 'sites');
        Guard::atLeast($tones, 2, 'tones');
        Guard::atMost($tones, $sites, 'tones', 'sites');

        return new self($size, $cells, $sites, $tones, $seed, $grid, $seeds);
    }

    public function tileWidth(): float
    {
        return $this->size;
    }

    public function tileHeight(): float
    {
        return $this->size;
    }

    protected function shapes(): array
    {
        $positions = $this->positions();
        $shapes = $this->squares($positions);

        if ($this->grid) {
            $shapes[] = $this->rules();
        }

        if ($this->seeds) {
            $shapes = [...$shapes, ...$this->dots($positions)];
        }

        return $shapes;
    }

    protected function geometry(): array
    {
        return [
            $this->size,
            $this->cells,
            $this->sites,
            $this->tones,
            $this->seed,
            $this->grid ? 1.0 : 0.0,
            $this->seeds ? 1.0 : 0.0,
        ];
    }

    protected function slug(): string
    {
        return 'mosaic';
    }

    /**
     * The seeds thrown into the tile.
     *
     * @return list<array{float, float}>
     */
    private function positions(): array
    {
        $random = new Random($this->seed);
        $positions = [];

        for ($site = 0; $site < $this->sites; ++$site) {
            $positions[] = [$random->float() * $this->size, $random->float() * $this->size];
        }

        return $positions;
    }

    /**
     * One path per tone, holding every square that seed indices sharing that
     * tone have taken.
     *
     * @param list<array{float, float}> $positions
     *
     * @return list<ElementInterface>
     */
    private function squares(array $positions): array
    {
        $step = $this->size / $this->cells;
        $side = Num::format($step);
        $runs = [];

        for ($row = 0; $row < $this->cells; ++$row) {
            for ($column = 0; $column < $this->cells; ++$column) {
                $level = $this->nearest($positions, ($column + 0.5) * $step, ($row + 0.5) * $step) % $this->tones;

                $runs[$level][] = 'M '.Num::format($column * $step).' '.Num::format($row * $step)
                    .' h '.$side.' v '.$side.' h -'.$side.' z';
            }
        }

        // Paths come out palest first, whatever order the tones were met in.
        ksort($runs);

        $shapes = [];

        foreach ($runs as $level => $subpaths) {
            $tone = new PathElement();
            $tone->setPathData(implode(' ', $subpaths));
            $tone->setAttribute('fill-opacity', Num::format($this->toneOf($level)));

            $shapes[] = $this->painted($tone);
        }

        return $shapes;
    }

    /**
     * The index of the seed nearest a point, measured on the torus.
     *
     * @param list<array{float, float}> $positions
     */
    private function nearest(array $positions, float $x, float $y): int
    {
        $nearest = 0;
        $shortest = \INF;

        foreach ($positions as $site => [$sx, $sy]) {
            $dx = $this->span($x - $sx);
            $dy = $this->span($y - $sy);
            $distance = $dx * $dx + $dy * $dy;

            if ($distance < $shortest) {
                $shortest = $distance;
                $nearest = $site;
            }
        }

        return $nearest;
    }

    /**
     * The distance along one axis on the torus: the shorter of the two ways,
     * across the tile or through the edges.
     */
    private function span(float $delta): float
    {
        $delta = abs($delta);

        return min($delta, $this->size - $delta);
    }

    /**
     * The opacity a tone level is painted at, the levels spread evenly between
     * the two bounds.
     */
    private function toneOf(int $level): float
    {
        return self::MIN_OPACITY + $level * (self::MAX_OPACITY - self::MIN_OPACITY) / ($this->tones - 1);
    }

    /**
     * The rules between the squares, as lines belonging to the tile.
     *
     * Both edges of the tile carry one, and each renders the half of it the
     * clip leaves: the other half comes from the tile next door, drawn against
     * its own edge. Outlining every square instead would lay two rules on every
     * inner joint, and two more along the seam, where the double width reads as
     * a cross drawn over the surface.
     */
    private function rules(): ElementInterface
    {
        $step = $this->size / $this->cells;
        $side = Num::format($this->size);
        $subpaths = [];

        for ($line = 0; $line <= $this->cells; ++$line) {
            $offset = Num::format($line * $step);

            $subpaths[] = 'M '.$offset.' 0 V '.$side;
            $subpaths[] = 'M 0 '.$offset.' H '.$side;
        }

        // One path, so the crossings are painted once and not twice.
        $rules = new PathElement();
        $rules->setPathData(implode(' ', $subpaths));
        $rules->setAttribute('stroke-opacity', Num::format(self::GRID_OPACITY));

        return $this->outlined($rules, $step * self::GRID_THICKNESS);
    }

    /**
     * A dot on each seed, repeated a period away when it overhangs an edge.
     *
     * @param list<array{float, float}> $positions
     *
     * @return list<ElementInterface>
     */
    private function dots(array $positions): array
    {
        $radius = $this->size * self::SEED_RADIUS;
        $shapes = [];

        foreach ($positions as [$sx, $sy]) {
            $offsets = Wrap::offsets($sx - $radius, $sy - $radius, $sx + $radius, $sy + $radius, $this->size, $this->size);

            foreach ($offsets as [$dx, $dy]) {
                $dot = new CircleElement();
                $dot->setCx(Num::format($sx + $dx));
                $dot->setCy(Num::format($sy + $dy));
                $dot->setR(Num::format($radius));
                $dot->setAttribute('fill-opacity', Num::format(self::SEED_OPACITY));

                $shapes[] = $this->painted($dot);
            }
        }

        return $shapes;
    }
}
