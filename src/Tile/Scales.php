<?php

declare(strict_types=1);

namespace Atelier\Pattern\Tile;

use Atelier\Pattern\AbstractPattern;
use Atelier\Pattern\Internal\Guard;
use Atelier\Pattern\Internal\Num;
use Atelier\Svg\Element\PathElement;

/**
 * Overlapping arcs, laid out like fish scales.
 *
 * Each arc is a half ellipse one width across and one height tall. Rows sit
 * one height apart and shift by half a width, so an arc apex lands on the
 * junction of the two arcs above it. The tile spans two rows.
 *
 * The shifted rows straddle the horizontal edges, so they are emitted at both
 * ends of the tile, a full period apart.
 */
final class Scales extends AbstractPattern
{
    private function __construct(
        private readonly float $width,
        private readonly float $height,
        private readonly float $thickness,
    ) {
    }

    /**
     * @param float $width     span of one arc
     * @param float $height    rise of one arc, and the distance between rows
     * @param float $thickness line width, at most the height
     */
    public static function create(float $width = 18.0, float $height = 12.0, float $thickness = 1.0): self
    {
        Guard::positive($width, 'width');
        Guard::positive($height, 'height');
        Guard::positive($thickness, 'thickness');
        Guard::atMost($thickness, $height, 'thickness', 'height');

        return new self($width, $height, $thickness);
    }

    public function tileWidth(): float
    {
        return $this->width;
    }

    public function tileHeight(): float
    {
        return 2 * $this->height;
    }

    protected function shapes(): array
    {
        $w = $this->width;
        $h = $this->height;

        // Aligned row, fully inside. Its two ends land on the vertical edges and
        // complete one another.
        $subpaths = [$this->arc(0.0, 1.5 * $h)];

        // Shifted rows, half a width across. The row above the aligned one and
        // the row below are the same geometry a period apart.
        foreach ([0.5 * $h, 2.5 * $h] as $baseline) {
            $subpaths[] = $this->arc(-$w / 2, $baseline);
            $subpaths[] = $this->arc($w / 2, $baseline);
        }

        $path = new PathElement();
        $path->setPathData(implode(' ', $subpaths));

        return [$this->outlined($path, $this->thickness)];
    }

    protected function geometry(): array
    {
        return [$this->width, $this->height, $this->thickness];
    }

    protected function slug(): string
    {
        return 'scales';
    }

    /**
     * One arc rising from (x, y) to (x + width, y), apex height above the
     * baseline.
     */
    private function arc(float $x, float $y): string
    {
        return \sprintf(
            'M %s %s A %s %s 0 0 1 %s %s',
            Num::format($x),
            Num::format($y),
            Num::format($this->width / 2),
            Num::format($this->height),
            Num::format($x + $this->width),
            Num::format($y),
        );
    }
}
