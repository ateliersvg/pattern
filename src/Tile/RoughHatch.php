<?php

declare(strict_types=1);

namespace Atelier\Pattern\Tile;

use Atelier\Pattern\AbstractPattern;
use Atelier\Pattern\Internal\Guard;
use Atelier\Pattern\Internal\Num;
use Atelier\Pattern\Internal\Random;
use Atelier\Pattern\Internal\Wrap;
use Atelier\Svg\Element\PathElement;

/**
 * Hatching drawn by an unsteady hand: the spacing between two strokes and the
 * inclination of each segment both wander.
 *
 * A stroke crosses the tile from the top edge to the bottom edge, bending at
 * every row. Its two ends share one abscissa, so it meets the stroke of the
 * tile above and the stroke of the tile below head on. Round caps close the
 * junction: two ends meeting on the seam read as one continuous line.
 *
 * The first stroke stands on the left edge, and any stroke wandering past a
 * vertical edge is drawn a second time one period away, against the opposite
 * edge.
 */
final class RoughHatch extends AbstractPattern
{
    /** Beyond this a stroke would leave its own column. */
    private const float MAX_JITTER = 0.25;

    private function __construct(
        private readonly float $spacing,
        private readonly float $thickness,
        private readonly float $jitter,
        private readonly int $cells,
        private readonly int $seed,
    ) {
    }

    /**
     * @param float $spacing   distance between two strokes
     * @param float $thickness line width, at most the spacing
     * @param float $jitter    how far a stroke wanders, as a share of the spacing, at most 0.25
     * @param int   $cells     strokes and rows of the repeating tile, at least 2
     * @param int   $seed      same seed, same drawing
     */
    public static function create(float $spacing = 14.0, float $thickness = 1.4, float $jitter = 0.25, int $cells = 6, int $seed = 1): self
    {
        Guard::positive($spacing, 'spacing');
        Guard::positive($thickness, 'thickness');
        Guard::atMost($thickness, $spacing, 'thickness', 'spacing');
        Guard::between($jitter, 0.0, self::MAX_JITTER, 'jitter');
        Guard::atLeast($cells, 2, 'cells');

        return new self($spacing, $thickness, $jitter, $cells, $seed);
    }

    public function tileWidth(): float
    {
        return $this->cells * $this->spacing;
    }

    public function tileHeight(): float
    {
        return $this->cells * $this->spacing;
    }

    protected function shapes(): array
    {
        $random = new Random($this->seed);
        $width = $this->tileWidth();
        $height = $this->tileHeight();
        $reach = $this->jitter * $this->spacing;
        $margin = $this->thickness / 2;

        $subpaths = [];

        for ($stroke = 0; $stroke < $this->cells; ++$stroke) {
            // The stroke leaves its own mark, which is what makes the spacing
            // uneven from one stroke to the next.
            $mark = $stroke * $this->spacing + $random->between(-$reach, $reach);

            $points = [[$mark + $random->between(-$reach, $reach), 0.0]];

            // The bends of one stroke sit at their own heights, so they do not
            // line up with the bends of the stroke next to it.
            for ($node = 1; $node < $this->cells; ++$node) {
                $points[] = [
                    $mark + $random->between(-$reach, $reach),
                    ($node + $random->between(-$this->jitter, $this->jitter)) * $this->spacing,
                ];
            }

            // The last node repeats the first, one period down: the two ends of
            // the stroke share an abscissa, and the tiles above and below carry
            // on from exactly where this one stops.
            $points[] = [$points[0][0], $height];

            $abscissae = array_column($points, 0);

            $offsets = Wrap::offsets(
                min($abscissae) - $margin,
                0.0,
                max($abscissae) + $margin,
                $height,
                $width,
                $height,
            );

            foreach ($offsets as [$dx, $dy]) {
                $subpaths[] = $this->polyline($points, $dx, $dy);
            }
        }

        $path = new PathElement();
        $path->setPathData(implode(' ', $subpaths));
        $path->setAttribute('stroke-linecap', 'round');
        $path->setAttribute('stroke-linejoin', 'round');

        return [$this->outlined($path, $this->thickness)];
    }

    protected function geometry(): array
    {
        return [$this->spacing, $this->thickness, $this->jitter, $this->cells, $this->seed];
    }

    protected function slug(): string
    {
        return 'rough-hatch';
    }

    /**
     * One stroke, node by node, moved by (dx, dy).
     *
     * @param list<array{float, float}> $points from the top edge to the bottom edge
     */
    private function polyline(array $points, float $dx, float $dy): string
    {
        $commands = [];

        foreach ($points as $node => [$x, $y]) {
            $commands[] = (0 === $node ? 'M ' : 'L ').Num::format($x + $dx).' '.Num::format($y + $dy);
        }

        return implode(' ', $commands);
    }
}
