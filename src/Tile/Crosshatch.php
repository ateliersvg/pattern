<?php

declare(strict_types=1);

namespace Atelier\Pattern\Tile;

use Atelier\Pattern\AbstractPattern;
use Atelier\Pattern\Internal\Guard;
use Atelier\Pattern\Internal\Num;
use Atelier\Svg\Element\PathElement;

/**
 * Two diagonals crossing inside the tile.
 *
 * Each diagonal runs corner to corner, so its butt caps meet the caps of the
 * neighbouring tiles head on and the lines read as continuous.
 *
 * The angle turns the crossing itself: at 45 degrees the two diagonals stand
 * upright and flat. It is given at construction or later through withAngle(),
 * and either way the rotation carries the tiling, not the lines.
 */
final class Crosshatch extends AbstractPattern
{
    private function __construct(
        private readonly float $spacing,
        private readonly float $thickness,
    ) {
    }

    /**
     * @param float $spacing   distance between two parallel diagonals
     * @param float $thickness line width, at most the spacing
     * @param float $angle     inclination of the crossing, in degrees
     */
    public static function create(float $spacing = 12.0, float $thickness = 1.2, float $angle = 0.0): self
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
        $s = Num::format($this->spacing);

        $path = new PathElement();
        $path->setPathData(\sprintf('M 0 0 L %1$s %1$s M %1$s 0 L 0 %1$s', $s));

        return [$this->outlined($path, $this->thickness)];
    }

    protected function geometry(): array
    {
        return [$this->spacing, $this->thickness];
    }

    protected function slug(): string
    {
        return 'crosshatch';
    }
}
