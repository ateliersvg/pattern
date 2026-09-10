<?php

declare(strict_types=1);

namespace Atelier\Pattern\Tile;

use Atelier\Pattern\AbstractPattern;
use Atelier\Pattern\Internal\Guard;
use Atelier\Pattern\Internal\Num;
use Atelier\Svg\Element\Shape\RectElement;

/**
 * Square cells drawn with one horizontal and one vertical rule.
 *
 * The rules are filled rectangles anchored on the tile edges, not centred
 * strokes: a stroke sitting on an edge loses half its width to the clip and
 * renders thinner than asked.
 */
final class Grid extends AbstractPattern
{
    private function __construct(
        private readonly float $size,
        private readonly float $thickness,
    ) {
    }

    /**
     * @param float $size      cell side
     * @param float $thickness rule width, at most the cell side
     */
    public static function create(float $size = 18.0, float $thickness = 1.0): self
    {
        Guard::positive($size, 'size');
        Guard::positive($thickness, 'thickness');
        Guard::atMost($thickness, $size, 'thickness', 'size');

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
        $size = Num::format($this->size);
        $thickness = Num::format($this->thickness);

        $vertical = new RectElement();
        $vertical->setX('0');
        $vertical->setY('0');
        $vertical->setWidth($thickness);
        $vertical->setHeight($size);

        $horizontal = new RectElement();
        $horizontal->setX('0');
        $horizontal->setY('0');
        $horizontal->setWidth($size);
        $horizontal->setHeight($thickness);

        return [$this->painted($vertical), $this->painted($horizontal)];
    }

    protected function geometry(): array
    {
        return [$this->size, $this->thickness];
    }

    protected function slug(): string
    {
        return 'grid';
    }
}
