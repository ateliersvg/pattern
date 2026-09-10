<?php

declare(strict_types=1);

namespace Atelier\Pattern\Tile;

use Atelier\Pattern\AbstractPattern;
use Atelier\Pattern\Internal\Guard;
use Atelier\Pattern\Internal\Num;
use Atelier\Pattern\Internal\Wrap;
use Atelier\Svg\Element\Shape\CircleElement;

/**
 * The flower of life: circles on a triangular lattice, spaced by their own
 * radius, so every circle passes through the middles of the six around it.
 *
 * That spacing is the whole figure. It is what turns the overlaps into the
 * six-petal rosette, and it is why the lattice is the one asanoha and honeycomb
 * already stand on.
 *
 * A circle is two tiles wide, so each one is drawn again on the far side of
 * every edge it leaves.
 */
final class FlowerOfLife extends AbstractPattern
{
    private function __construct(
        private readonly float $radius,
        private readonly float $thickness,
    ) {
    }

    /**
     * @param float $radius    radius of one circle, which is also the spacing
     * @param float $thickness line width, at most an eighth of the radius
     */
    public static function create(float $radius = 25.0, float $thickness = 1.0): self
    {
        Guard::positive($radius, 'radius');
        Guard::positive($thickness, 'thickness');
        Guard::atMost($thickness, $radius / 8, 'thickness', 'an eighth of the radius');

        return new self($radius, $thickness);
    }

    public function tileWidth(): float
    {
        return $this->radius;
    }

    public function tileHeight(): float
    {
        return $this->radius * sqrt(3);
    }

    protected function shapes(): array
    {
        $shapes = [];

        // One circle per row of the lattice, the second row half a step across.
        $centres = [
            [0.0, 0.0],
            [$this->radius / 2, $this->radius * sqrt(3) / 2],
        ];

        foreach ($centres as [$cx, $cy]) {
            $offsets = Wrap::offsets(
                $cx - $this->radius,
                $cy - $this->radius,
                $cx + $this->radius,
                $cy + $this->radius,
                $this->tileWidth(),
                $this->tileHeight(),
            );

            foreach ($offsets as [$dx, $dy]) {
                $circle = new CircleElement();
                $circle->setCx(Num::format($cx + $dx));
                $circle->setCy(Num::format($cy + $dy));
                $circle->setR(Num::format($this->radius));

                $shapes[] = $this->outlined($circle, $this->thickness);
            }
        }

        return $shapes;
    }

    protected function geometry(): array
    {
        return [$this->radius, $this->thickness];
    }

    protected function slug(): string
    {
        return 'flower-of-life';
    }
}
