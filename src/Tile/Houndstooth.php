<?php

declare(strict_types=1);

namespace Atelier\Pattern\Tile;

use Atelier\Pattern\AbstractPattern;
use Atelier\Pattern\Internal\Guard;
use Atelier\Pattern\Internal\Num;
use Atelier\Pattern\Internal\Wrap;
use Atelier\Svg\Element\PathElement;

/**
 * Houndstooth, the broken check of the woven pied-de-poule.
 *
 * The tile is four sub-units each way, a sub-unit being a quarter of the size.
 * It holds a square two sub-units across and two bands one sub-unit wide that
 * leave it at 45 degrees: one from its right side, across the cell on the
 * right, and one from its lower edge, across the cell below. A band runs off
 * the far side of the cell it crosses and comes back in on the near side, so
 * each is drawn as two pieces.
 *
 * That is the whole rule, and it is what makes the check a broken one: what the
 * tile leaves empty is the same motif, moved half a tile across and half a tile
 * down. Half the surface each, one shape.
 *
 * The five pieces go into a single path, so the edges they share are interior
 * to one fill and no seam runs between them. Nothing crosses a tile edge while
 * the motif is only filled; a line width pushes it past three of them, and the
 * pieces that cross are then drawn again a period away.
 */
final class Houndstooth extends AbstractPattern
{
    private function __construct(
        private readonly float $size,
        private readonly float $thickness,
    ) {
    }

    /**
     * @param float $size      side of the tile, four sub-units
     * @param float $thickness line laid on the fill to fatten the motif, at most a sub-unit; 0 leaves the fill alone
     */
    public static function create(float $size = 24.0, float $thickness = 0.0): self
    {
        Guard::positive($size, 'size');
        Guard::between($thickness, 0.0, $size / 4, 'thickness');

        return new self($size, $thickness);
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
        $subpaths = [];

        foreach ($this->pieces() as $piece) {
            foreach ($this->offsetsFor($piece) as [$dx, $dy]) {
                $subpaths[] = $this->outline($piece, $dx, $dy);
            }
        }

        $path = new PathElement();
        $path->setPathData(implode(' ', $subpaths));

        $shape = $this->painted($path);

        if ($this->thickness > 0.0) {
            $shape->setAttribute('stroke', $this->color());
            $shape->setAttribute('stroke-width', Num::format($this->thickness));
        }

        return [$shape];
    }

    protected function geometry(): array
    {
        return [$this->size, $this->thickness];
    }

    protected function slug(): string
    {
        return 'houndstooth';
    }

    /**
     * @return list<non-empty-list<array{float, float}>>
     */
    private function pieces(): array
    {
        $unit = $this->size / 4;

        return [
            // The square.
            [[0.0, 0.0], [2 * $unit, 0.0], [2 * $unit, 2 * $unit], [0.0, 2 * $unit]],
            // The arm, leaving the right side of the square for the cell on the
            // right, then the piece of it that came back in on the near side.
            [[2 * $unit, 0.0], [4 * $unit, 2 * $unit], [3 * $unit, 2 * $unit], [2 * $unit, $unit]],
            [[3 * $unit, 0.0], [4 * $unit, 0.0], [4 * $unit, $unit]],
            // The leg, the same thing downwards from the lower edge.
            [[0.0, 2 * $unit], [$unit, 2 * $unit], [2 * $unit, 3 * $unit], [2 * $unit, 4 * $unit]],
            [[0.0, 3 * $unit], [$unit, 4 * $unit], [0.0, 4 * $unit]],
        ];
    }

    /**
     * @param non-empty-list<array{float, float}> $piece
     *
     * @return list<array{float, float}>
     */
    private function offsetsFor(array $piece): array
    {
        $margin = $this->thickness / 2;

        [$minX, $minY] = $piece[0];
        $maxX = $minX;
        $maxY = $minY;

        foreach ($piece as [$x, $y]) {
            $minX = min($minX, $x);
            $maxX = max($maxX, $x);
            $minY = min($minY, $y);
            $maxY = max($maxY, $y);
        }

        return Wrap::offsets($minX - $margin, $minY - $margin, $maxX + $margin, $maxY + $margin, $this->size, $this->size);
    }

    /**
     * @param list<array{float, float}> $piece
     */
    private function outline(array $piece, float $dx, float $dy): string
    {
        $commands = [];

        foreach ($piece as $index => [$x, $y]) {
            $commands[] = (0 === $index ? 'M ' : 'L ').Num::format($x + $dx).' '.Num::format($y + $dy);
        }

        return implode(' ', $commands).' Z';
    }
}
