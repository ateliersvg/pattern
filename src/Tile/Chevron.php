<?php

declare(strict_types=1);

namespace Atelier\Pattern\Tile;

use Atelier\Pattern\AbstractPattern;
use Atelier\Pattern\Internal\Guard;
use Atelier\Pattern\Internal\Num;
use Atelier\Svg\Element\PathElement;

/**
 * Stacked zigzags, peaks aligned across rows.
 *
 * One zigzag is size wide and half a size tall, and rows repeat every half
 * size, so the tile is size by size / 2.
 *
 * Peaks and troughs land on the horizontal edges. The rows above and below are
 * emitted too: clipped to the tile they contribute only the tips their
 * neighbours cut off, which is what keeps the corners sharp across the seam.
 * Each zigzag also runs half a period past both vertical edges, so its corners
 * are mitred rather than capped at the boundary.
 */
final class Chevron extends AbstractPattern
{
    private function __construct(
        private readonly float $size,
        private readonly float $thickness,
    ) {
    }

    /**
     * @param float $size      distance between two peaks of the same row
     * @param float $thickness line width, at most half the size
     */
    public static function create(float $size = 20.0, float $thickness = 2.0): self
    {
        Guard::positive($size, 'size');
        Guard::positive($thickness, 'thickness');
        Guard::atMost($thickness, $size / 2, 'thickness', 'half the size');

        return new self($size, $thickness);
    }

    public function tileWidth(): float
    {
        return $this->size;
    }

    public function tileHeight(): float
    {
        return $this->size / 2;
    }

    protected function shapes(): array
    {
        $rise = $this->tileHeight();

        $subpaths = [];

        foreach ([-$rise, 0.0, $rise] as $offset) {
            $subpaths[] = $this->zigzag($offset);
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
        return 'chevron';
    }

    /**
     * One zigzag, peaks on the baseline, extended half a period each side.
     */
    private function zigzag(float $baseline): string
    {
        $half = $this->size / 2;
        $rise = $this->tileHeight();

        $points = [
            [-$half, $baseline + $rise],
            [0.0, $baseline],
            [$half, $baseline + $rise],
            [$this->size, $baseline],
            [$this->size + $half, $baseline + $rise],
        ];

        $commands = [];

        foreach ($points as $index => [$x, $y]) {
            $commands[] = (0 === $index ? 'M ' : 'L ').Num::format($x).' '.Num::format($y);
        }

        return implode(' ', $commands);
    }
}
