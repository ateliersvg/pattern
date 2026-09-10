<?php

declare(strict_types=1);

namespace Atelier\Pattern\Tile;

use Atelier\Pattern\AbstractPattern;
use Atelier\Pattern\Exception\InvalidArgumentException;
use Atelier\Pattern\Internal\Guard;
use Atelier\Pattern\Internal\Num;
use Atelier\Pattern\Internal\Random;
use Atelier\Pattern\Internal\Wrap;
use Atelier\Svg\Element\Shape\PolygonElement;

/**
 * A Voronoi diagram computed on a torus: cells of unequal size and shape that
 * still run on across the seam.
 *
 * Seeds are thrown into the square tile, then copied onto the eight
 * neighbouring tiles. A cell is the set of points closer to its seed than to
 * any seed of that whole neighbourhood, obtained by cutting a box with the
 * perpendicular bisector of each pair. Because the copies stand exactly one
 * period away, what bounds a cell on one edge bounds its neighbour on the
 * opposite edge: the diagram joins by construction, with no border case.
 *
 * Filled, each cell takes an opacity of its own, drawn between two bounds. The
 * tone belongs to the seed and not to the polygon: a cell reaching over an edge
 * is drawn a second time a period away, and both copies carry the tone of the
 * one seed they come from, so a cell cut by an edge is one flat tone and not
 * two halves of different shades.
 *
 * A cell is stroked whole, so an edge shared by two cells is drawn twice. Both
 * strokes are the same segment and cover one another. Every fill goes down
 * before the first stroke, so no cell paints over the outline of its neighbour.
 */
final class Voronoi extends AbstractPattern
{
    private function __construct(
        private readonly float $size,
        private readonly float $thickness,
        private readonly int $sites,
        private readonly int $seed,
        private readonly bool $filled,
        private readonly float $minOpacity,
        private readonly float $maxOpacity,
    ) {
    }

    /**
     * @param float $size       side of the square tile
     * @param float $thickness  line width, at most a quarter of the size; 0 leaves the fill alone
     * @param int   $sites      seeds thrown into the tile, at least 2
     * @param int   $seed       same seed, same diagram
     * @param bool  $filled     paints each cell with the pattern color at an opacity of its own
     * @param float $minOpacity opacity of the palest cell
     * @param float $maxOpacity opacity of the deepest cell
     *
     * @throws InvalidArgumentException if the cells are neither filled nor stroked
     */
    public static function create(float $size = 120.0, float $thickness = 1.4, int $sites = 14, int $seed = 1, bool $filled = false, float $minOpacity = 0.15, float $maxOpacity = 0.65): self
    {
        Guard::positive($size, 'size');
        Guard::between($thickness, 0.0, $size / 4, 'thickness');
        Guard::atLeast($sites, 2, 'sites');
        Guard::between($minOpacity, 0.0, 1.0, 'minOpacity');
        Guard::between($maxOpacity, 0.0, 1.0, 'maxOpacity');
        Guard::atMost($minOpacity, $maxOpacity, 'minOpacity', 'maxOpacity');

        if (!$filled && 0.0 === $thickness) {
            throw new InvalidArgumentException('thickness must be greater than 0 unless the cells are filled, got 0.');
        }

        return new self($size, $thickness, $sites, $seed, $filled, $minOpacity, $maxOpacity);
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
        $copies = $this->copies();
        $shapes = [];

        // Fills first, strokes after: an outline is shared by two cells, and a
        // fill laid down later would eat the half of it lying on its side.
        if ($this->filled) {
            foreach ($copies as [$points, $tone]) {
                $cell = new PolygonElement();
                $cell->setPoints($points);
                $cell->setAttribute('fill-opacity', Num::format($tone));

                $shapes[] = $this->painted($cell);
            }
        }

        if ($this->thickness > 0.0) {
            foreach ($copies as [$points]) {
                $cell = new PolygonElement();
                $cell->setPoints($points);

                $shapes[] = $this->outlined($cell, $this->thickness);
            }
        }

        return $shapes;
    }

    protected function geometry(): array
    {
        $geometry = [$this->size, $this->thickness, $this->sites, $this->seed];

        // The bounds only tell two tiles apart once the cells are filled.
        return $this->filled ? [...$geometry, 1.0, $this->minOpacity, $this->maxOpacity] : $geometry;
    }

    protected function slug(): string
    {
        return 'voronoi';
    }

    /**
     * Every cell of the tile, as the points of the polygon and the tone of the
     * seed it belongs to, repeated at each offset the joining rule asks for.
     *
     * @return list<array{string, float}>
     */
    private function copies(): array
    {
        $seeds = $this->seeds();
        $neighbourhood = $this->neighbourhoodOf($seeds);
        $margin = $this->thickness / 2;

        $copies = [];

        foreach ($seeds as [$sx, $sy, $tone]) {
            $cell = $this->cellOf($sx, $sy, $neighbourhood);

            // The seed lies inside its own cell, so starting the envelope on it
            // widens nothing and spares a test for an empty list.
            $minX = $maxX = $sx;
            $minY = $maxY = $sy;

            foreach ($cell as [$x, $y]) {
                $minX = min($minX, $x);
                $maxX = max($maxX, $x);
                $minY = min($minY, $y);
                $maxY = max($maxY, $y);
            }

            $offsets = Wrap::offsets($minX - $margin, $minY - $margin, $maxX + $margin, $maxY + $margin, $this->size, $this->size);

            foreach ($offsets as [$dx, $dy]) {
                $copies[] = [$this->points($cell, $dx, $dy), $tone];
            }
        }

        return $copies;
    }

    /**
     * The seeds thrown into the tile, each with the tone of its cell.
     *
     * Every position is drawn before the first tone, so asking for a fill never
     * moves a seed and the diagram stays the one the seed number names.
     *
     * @return list<array{float, float, float}>
     */
    private function seeds(): array
    {
        $random = new Random($this->seed);
        $positions = [];

        for ($site = 0; $site < $this->sites; ++$site) {
            $positions[] = [$random->float() * $this->size, $random->float() * $this->size];
        }

        $seeds = [];

        foreach ($positions as [$x, $y]) {
            $seeds[] = [$x, $y, $random->between($this->minOpacity, $this->maxOpacity)];
        }

        return $seeds;
    }

    /**
     * Every seed and its eight copies, one per neighbouring tile.
     *
     * @param list<array{float, float, float}> $seeds
     *
     * @return list<array{float, float}>
     */
    private function neighbourhoodOf(array $seeds): array
    {
        $neighbourhood = [];

        foreach ($seeds as [$sx, $sy]) {
            for ($column = -1; $column <= 1; ++$column) {
                for ($row = -1; $row <= 1; ++$row) {
                    $neighbourhood[] = [$sx + $column * $this->size, $sy + $row * $this->size];
                }
            }
        }

        return $neighbourhood;
    }

    /**
     * The cell of one seed, cut out of a box by the bisector of every pair.
     *
     * @param list<array{float, float}> $neighbourhood
     *
     * @return list<array{float, float}>
     */
    private function cellOf(float $sx, float $sy, array $neighbourhood): array
    {
        $half = $this->size / 2;

        // The bisector between a seed and its own copy one period away stands
        // half a period from it, so the cell never leaves this box.
        $cell = [
            [$sx - $half, $sy - $half],
            [$sx + $half, $sy - $half],
            [$sx + $half, $sy + $half],
            [$sx - $half, $sy + $half],
        ];

        foreach ($neighbourhood as [$rx, $ry]) {
            $dx = $rx - $sx;
            $dy = $ry - $sy;

            if (0.0 === $dx && 0.0 === $dy) {
                continue;
            }

            // Points at least as close to (sx, sy) as to (rx, ry) are those on
            // this side of the bisector of the two.
            $cell = $this->clip($cell, $dx, $dy, ($dx * ($sx + $rx) + $dy * ($sy + $ry)) / 2);
        }

        return $cell;
    }

    /**
     * The part of a convex polygon lying in the half plane nx*x + ny*y <= limit,
     * by the Sutherland-Hodgman cut.
     *
     * @param list<array{float, float}> $polygon
     *
     * @return list<array{float, float}>
     */
    private function clip(array $polygon, float $nx, float $ny, float $limit): array
    {
        $clipped = [];
        $corners = \count($polygon);

        for ($corner = 0; $corner < $corners; ++$corner) {
            [$fromX, $fromY] = $polygon[$corner];
            [$toX, $toY] = $polygon[($corner + 1) % $corners];

            $here = $nx * $fromX + $ny * $fromY - $limit;
            $there = $nx * $toX + $ny * $toY - $limit;

            if ($here <= 0.0) {
                $clipped[] = [$fromX, $fromY];
            }

            if (($here < 0.0 && $there > 0.0) || ($here > 0.0 && $there < 0.0)) {
                $ratio = $here / ($here - $there);
                $clipped[] = [$fromX + $ratio * ($toX - $fromX), $fromY + $ratio * ($toY - $fromY)];
            }
        }

        return $clipped;
    }

    /**
     * @param list<array{float, float}> $cell
     */
    private function points(array $cell, float $dx, float $dy): string
    {
        $points = [];

        foreach ($cell as [$x, $y]) {
            $points[] = Num::format($x + $dx).','.Num::format($y + $dy);
        }

        return implode(' ', $points);
    }
}
