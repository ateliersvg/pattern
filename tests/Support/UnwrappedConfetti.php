<?php

declare(strict_types=1);

namespace Atelier\Pattern\Tests\Support;

use Atelier\Pattern\AbstractPattern;
use Atelier\Svg\Element\Shape\PolygonElement;

/**
 * An irregular tile that breaks the joining rule on purpose.
 *
 * Four squares thrown across the tile. The first one landed on the left edge
 * and was left there: nothing is drawn against the right edge, so the clip cuts
 * it and the surface shows a square sliced in two at every tile boundary.
 */
final class UnwrappedConfetti extends AbstractPattern
{
    public function __construct(
        private readonly float $spacing = 20.0,
        private readonly float $size = 5.0,
    ) {
    }

    public function tileWidth(): float
    {
        return 2 * $this->spacing;
    }

    public function tileHeight(): float
    {
        return 2 * $this->spacing;
    }

    protected function shapes(): array
    {
        $shapes = [];

        foreach ([[2.0, 12.0], [14.0, 7.0], [9.0, 27.0], [31.0, 22.0]] as [$cx, $cy]) {
            $square = new PolygonElement();
            $square->setPoints(\sprintf(
                '%s,%s %s,%s %s,%s %s,%s',
                $cx - $this->size,
                $cy - $this->size,
                $cx + $this->size,
                $cy - $this->size,
                $cx + $this->size,
                $cy + $this->size,
                $cx - $this->size,
                $cy + $this->size,
            ));

            $shapes[] = $this->painted($square);
        }

        return $shapes;
    }

    protected function geometry(): array
    {
        return [$this->spacing, $this->size];
    }

    protected function slug(): string
    {
        return 'unwrapped-confetti';
    }
}
