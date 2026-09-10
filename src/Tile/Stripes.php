<?php

declare(strict_types=1);

namespace Atelier\Pattern\Tile;

use Atelier\Pattern\AbstractPattern;
use Atelier\Pattern\Internal\Guard;
use Atelier\Pattern\Internal\Num;
use Atelier\Svg\Element\Shape\RectElement;

/**
 * Parallel bands, vertical by default.
 *
 * The band is a filled rectangle held inside the tile, so nothing crosses an
 * edge. Diagonal stripes come from the angle, given at construction or later
 * through withAngle(): the rotation carries the tiling, not the band, so the
 * tile stays the same square whatever the inclination.
 */
final class Stripes extends AbstractPattern
{
    private function __construct(
        private readonly float $spacing,
        private readonly float $thickness,
    ) {
    }

    /**
     * @param float $spacing   distance between the left edges of two bands
     * @param float $thickness band width, at most the spacing
     * @param float $angle     inclination of the bands, in degrees
     */
    public static function create(float $spacing = 12.0, float $thickness = 4.0, float $angle = 0.0): self
    {
        Guard::positive($spacing, 'spacing');
        Guard::positive($thickness, 'thickness');
        Guard::atMost($thickness, $spacing, 'thickness', 'spacing');

        return (new self($spacing, $thickness))->withAngle($angle);
    }

    public function tileWidth(): float
    {
        return $this->spacing;
    }

    public function tileHeight(): float
    {
        return $this->spacing;
    }

    protected function shapes(): array
    {
        $band = new RectElement();
        $band->setX('0');
        $band->setY('0');
        $band->setWidth(Num::format($this->thickness));
        $band->setHeight(Num::format($this->spacing));

        return [$this->painted($band)];
    }

    protected function geometry(): array
    {
        return [$this->spacing, $this->thickness];
    }

    protected function slug(): string
    {
        return 'stripes';
    }
}
