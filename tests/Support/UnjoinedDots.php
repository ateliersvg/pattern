<?php

declare(strict_types=1);

namespace Atelier\Pattern\Tests\Support;

use Atelier\Pattern\AbstractPattern;
use Atelier\Svg\Element\Shape\CircleElement;

/**
 * A tile that breaks the joining rule on purpose.
 *
 * It reproduces the fault the catalogue is written against: a staggered dot
 * lattice whose shifted row is drawn only against the right edge, so the clip
 * cuts every dot of that row in half.
 */
final class UnjoinedDots extends AbstractPattern
{
    public function __construct(
        private readonly float $spacing = 16.0,
        private readonly float $radius = 3.0,
    ) {
    }

    public function tileWidth(): float
    {
        return $this->spacing;
    }

    public function tileHeight(): float
    {
        return 2 * $this->spacing;
    }

    protected function shapes(): array
    {
        $shapes = [];

        foreach ([[$this->spacing / 2, $this->spacing / 2], [$this->spacing, 1.5 * $this->spacing]] as [$cx, $cy]) {
            $dot = new CircleElement();
            $dot->setCx((string) $cx);
            $dot->setCy((string) $cy);
            $dot->setR((string) $this->radius);

            $shapes[] = $this->painted($dot);
        }

        return $shapes;
    }

    protected function geometry(): array
    {
        return [$this->spacing, $this->radius];
    }

    protected function slug(): string
    {
        return 'unjoined-dots';
    }
}
