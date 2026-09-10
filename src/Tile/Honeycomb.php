<?php

declare(strict_types=1);

namespace Atelier\Pattern\Tile;

use Atelier\Pattern\AbstractPattern;
use Atelier\Pattern\Internal\Guard;
use Atelier\Pattern\Internal\Hex;
use Atelier\Pattern\Internal\Num;
use Atelier\Svg\Element\Shape\PolygonElement;

/**
 * A hexagonal mesh, hexagons standing on a point.
 *
 * A hexagon of radius r is sqrt(3)*r wide and 2*r tall. Centres sit sqrt(3)*r
 * apart across and 1.5*r apart down, every other row shifted by half a width.
 * The smallest repeating tile is therefore sqrt(3)*r by 3*r and holds two rows.
 *
 * Five hexagons are emitted: the one that fits, and the four that straddle the
 * edges, each present on both sides so the clip is filled from the neighbour.
 */
final class Honeycomb extends AbstractPattern
{
    private function __construct(
        private readonly float $radius,
        private readonly float $thickness,
    ) {
    }

    /**
     * @param float $radius    distance from a hexagon centre to a vertex
     * @param float $thickness line width, at most the radius
     */
    public static function create(float $radius = 12.0, float $thickness = 1.1): self
    {
        Guard::positive($radius, 'radius');
        Guard::positive($thickness, 'thickness');
        Guard::atMost($thickness, $radius, 'thickness', 'radius');

        return new self($radius, $thickness);
    }

    public function tileWidth(): float
    {
        return Hex::tileWidth($this->radius);
    }

    public function tileHeight(): float
    {
        return Hex::tileHeight($this->radius);
    }

    protected function shapes(): array
    {
        $shapes = [];

        foreach (Hex::centres($this->radius) as [$cx, $cy]) {
            $hexagon = new PolygonElement();
            $hexagon->setPoints($this->hexagonPoints($cx, $cy));

            $shapes[] = $this->outlined($hexagon, $this->thickness);
        }

        return $shapes;
    }

    protected function geometry(): array
    {
        return [$this->radius, $this->thickness];
    }

    protected function slug(): string
    {
        return 'honeycomb';
    }

    private function hexagonPoints(float $cx, float $cy): string
    {
        $points = [];

        foreach (Hex::vertices($cx, $cy, $this->radius) as [$x, $y]) {
            $points[] = Num::format($x).','.Num::format($y);
        }

        return implode(' ', $points);
    }
}
