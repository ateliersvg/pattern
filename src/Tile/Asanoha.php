<?php

declare(strict_types=1);

namespace Atelier\Pattern\Tile;

use Atelier\Pattern\AbstractPattern;
use Atelier\Pattern\Internal\Guard;
use Atelier\Pattern\Internal\Hex;
use Atelier\Pattern\Internal\Num;
use Atelier\Svg\Element\PathElement;

/**
 * Asanoha, the Japanese hemp leaf.
 *
 * Each hexagon of the lattice is cut into six triangles by its three long
 * diagonals, and each triangle carries a spoke from each of its corners to its
 * middle. The six-pointed star that comes out of it is the leaf.
 *
 * The tile is the one of the hexagonal lattice, sqrt(3)*size by 3*size. Its
 * five hexagons cover it exactly: one in the middle, and four corner quarters
 * that are translates of one another a period away. Neighbouring hexagons share
 * a side, so that side is drawn twice, once by each; the two lines are the same
 * segment and cover one another.
 */
final class Asanoha extends AbstractPattern
{
    private function __construct(
        private readonly float $size,
        private readonly float $thickness,
    ) {
    }

    /**
     * @param float $size      distance from a hexagon middle to a vertex
     * @param float $thickness line width, at most a quarter of the size
     */
    public static function create(float $size = 28.0, float $thickness = 1.2): self
    {
        Guard::positive($size, 'size');
        Guard::positive($thickness, 'thickness');
        Guard::atMost($thickness, $size / 4, 'thickness', 'a quarter of the size');

        return new self($size, $thickness);
    }

    public function tileWidth(): float
    {
        return Hex::tileWidth($this->size);
    }

    public function tileHeight(): float
    {
        return Hex::tileHeight($this->size);
    }

    protected function shapes(): array
    {
        $subpaths = [];

        foreach (Hex::centres($this->size) as [$cx, $cy]) {
            $vertices = Hex::vertices($cx, $cy, $this->size);

            $subpaths[] = $this->line([...$vertices, $vertices[0]]);

            // The three long diagonals, which cut the hexagon into six
            // triangles.
            foreach ([[0, 3], [1, 4], [2, 5]] as [$from, $to]) {
                $subpaths[] = $this->line([$vertices[$from], $vertices[$to]]);
            }

            // Each triangle stands on one side of the hexagon. Its three spokes
            // meet at its middle, two of them drawn in one stroke.
            foreach ([[0, 1], [1, 2], [2, 3], [3, 4], [4, 5], [5, 0]] as [$from, $to]) {
                $near = $vertices[$from];
                $far = $vertices[$to];
                $middle = [($cx + $near[0] + $far[0]) / 3, ($cy + $near[1] + $far[1]) / 3];

                $subpaths[] = $this->line([$near, $middle, $far]);
                $subpaths[] = $this->line([[$cx, $cy], $middle]);
            }
        }

        $path = new PathElement();
        $path->setPathData(implode(' ', $subpaths));

        return [$this->outlined($path, $this->thickness)];
    }

    protected function geometry(): array
    {
        return [$this->size, $this->thickness];
    }

    protected function slug(): string
    {
        return 'asanoha';
    }

    /**
     * @param list<array{float, float}> $points
     */
    private function line(array $points): string
    {
        $commands = [];

        foreach ($points as $index => [$x, $y]) {
            $commands[] = (0 === $index ? 'M ' : 'L ').Num::format($x).' '.Num::format($y);
        }

        return implode(' ', $commands);
    }
}
