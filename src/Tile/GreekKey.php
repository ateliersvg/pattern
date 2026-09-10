<?php

declare(strict_types=1);

namespace Atelier\Pattern\Tile;

use Atelier\Pattern\AbstractPattern;
use Atelier\Pattern\Internal\Guard;
use Atelier\Pattern\Internal\Num;
use Atelier\Svg\Element\PathElement;

/**
 * The Greek fret: a key spiralling off a rule, repeated along it.
 *
 * The tile is eight units square. A rule runs the full width, and the key hangs
 * from it: up the left of the cell, along the top, down the right, and back in
 * on itself to the middle. The rule is what makes a row read as one band rather
 * than as a line of separate stamps.
 *
 * Nothing crosses an edge. The rule ends exactly on both of them and the key
 * stands a unit inside, so the tile joins without a single shape being drawn
 * twice.
 */
final class GreekKey extends AbstractPattern
{
    private function __construct(
        private readonly float $size,
        private readonly float $thickness,
    ) {
    }

    /**
     * @param float $size      side of the tile, eight units
     * @param float $thickness line width, at most one unit
     */
    public static function create(float $size = 40.0, float $thickness = 1.4): self
    {
        Guard::positive($size, 'size');
        Guard::positive($thickness, 'thickness');
        Guard::atMost($thickness, $size / 8, 'thickness', 'an eighth of the size');

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
        $path = new PathElement();
        $path->setPathData(implode(' ', [
            $this->line([[0, 7], [8, 7]]),
            $this->line([[1, 7], [1, 1], [7, 1], [7, 5], [3, 5], [3, 3], [5, 3]]),
        ]));

        return [$this->outlined($path, $this->thickness)];
    }

    protected function geometry(): array
    {
        return [$this->size, $this->thickness];
    }

    protected function slug(): string
    {
        return 'greek-key';
    }

    /**
     * @param list<array{int, int}> $points in units of an eighth of the tile
     */
    private function line(array $points): string
    {
        $unit = $this->size / 8;
        $commands = [];

        foreach ($points as $index => [$x, $y]) {
            $commands[] = (0 === $index ? 'M ' : 'L ').Num::format($x * $unit).' '.Num::format($y * $unit);
        }

        return implode(' ', $commands);
    }
}
