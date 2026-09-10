<?php

declare(strict_types=1);

namespace Atelier\Pattern\Tile;

use Atelier\Pattern\AbstractPattern;
use Atelier\Pattern\Internal\Guard;
use Atelier\Pattern\Internal\Num;
use Atelier\Svg\Element\Shape\RectElement;

/**
 * A checkerboard: two filled cells on a diagonal, two left transparent.
 *
 * The tile spans two cells each way and both squares sit inside it, so nothing
 * crosses an edge.
 */
final class Checker extends AbstractPattern
{
    private function __construct(
        private readonly float $size,
    ) {
    }

    /**
     * @param float $size side of one cell
     */
    public static function create(float $size = 12.0): self
    {
        Guard::positive($size, 'size');

        return new self($size);
    }

    public function tileWidth(): float
    {
        return 2 * $this->size;
    }

    public function tileHeight(): float
    {
        return 2 * $this->size;
    }

    protected function shapes(): array
    {
        $size = Num::format($this->size);
        $shapes = [];

        foreach ([[0.0, 0.0], [$this->size, $this->size]] as [$x, $y]) {
            $cell = new RectElement();
            $cell->setX(Num::format($x));
            $cell->setY(Num::format($y));
            $cell->setWidth($size);
            $cell->setHeight($size);

            $shapes[] = $this->painted($cell);
        }

        return $shapes;
    }

    protected function geometry(): array
    {
        return [$this->size];
    }

    protected function slug(): string
    {
        return 'checker';
    }
}
