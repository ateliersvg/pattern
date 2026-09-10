<?php

declare(strict_types=1);

namespace Atelier\Pattern\Tile;

use Atelier\Pattern\AbstractPattern;
use Atelier\Pattern\Internal\Guard;
use Atelier\Pattern\Internal\Num;
use Atelier\Pattern\Internal\Random;
use Atelier\Svg\Element\PathElement;

/**
 * A Truchet paving: quarter circles turned at random, reading as one tangle of
 * curves.
 *
 * A cell carries two quarter circles of radius half a cell, centred on two
 * opposite corners. Which of the two diagonals they sit on is drawn at random,
 * and that is the whole of the disorder.
 *
 * Either way the four ends land on the four edge midpoints of the cell, and
 * each one meets its neighbour at a right angle to the shared edge. The cells
 * therefore join whatever they were dealt, and so do the tiles: the macro tile
 * is a square of cells by cells, and the disorder stops at its border.
 */
final class Truchet extends AbstractPattern
{
    private function __construct(
        private readonly float $tile,
        private readonly float $thickness,
        private readonly int $cells,
        private readonly int $seed,
    ) {
    }

    /**
     * @param float $tile      side of one cell
     * @param float $thickness line width, at most half a cell
     * @param int   $cells     cells per side of the repeating tile, at least 2
     * @param int   $seed      same seed, same drawing
     */
    public static function create(float $tile = 24.0, float $thickness = 2.5, int $cells = 8, int $seed = 1): self
    {
        Guard::positive($tile, 'tile');
        Guard::positive($thickness, 'thickness');
        Guard::atMost($thickness, $tile / 2, 'thickness', 'half the tile');
        Guard::atLeast($cells, 2, 'cells');

        return new self($tile, $thickness, $cells, $seed);
    }

    public function tileWidth(): float
    {
        return $this->cells * $this->tile;
    }

    public function tileHeight(): float
    {
        return $this->cells * $this->tile;
    }

    protected function shapes(): array
    {
        $random = new Random($this->seed);
        $subpaths = [];

        for ($row = 0; $row < $this->cells; ++$row) {
            for ($column = 0; $column < $this->cells; ++$column) {
                $x = $column * $this->tile;
                $y = $row * $this->tile;

                // The two corners the quarters are centred on, and the angle
                // each quarter starts at so that it curves inside the cell.
                $corners = 0 === $random->int(0, 1)
                    ? [[$x, $y, 0.0], [$x + $this->tile, $y + $this->tile, 180.0]]
                    : [[$x + $this->tile, $y, 90.0], [$x, $y + $this->tile, 270.0]];

                foreach ($corners as [$cx, $cy, $start]) {
                    $subpaths[] = $this->quarter($cx, $cy, $start);
                }
            }
        }

        $path = new PathElement();
        $path->setPathData(implode(' ', $subpaths));

        return [$this->outlined($path, $this->thickness)];
    }

    protected function geometry(): array
    {
        return [$this->tile, $this->thickness, $this->cells, $this->seed];
    }

    protected function slug(): string
    {
        return 'truchet';
    }

    /**
     * A quarter circle around (cx, cy), from the given angle to a quarter turn
     * later. Both ends sit on an edge midpoint of the cell.
     */
    private function quarter(float $cx, float $cy, float $start): string
    {
        $radius = $this->tile / 2;
        $from = deg2rad($start);
        $to = deg2rad($start + 90.0);

        return \sprintf(
            'M %s %s A %s %s 0 0 1 %s %s',
            Num::format($cx + cos($from) * $radius),
            Num::format($cy + sin($from) * $radius),
            Num::format($radius),
            Num::format($radius),
            Num::format($cx + cos($to) * $radius),
            Num::format($cy + sin($to) * $radius),
        );
    }
}
