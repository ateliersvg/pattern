<?php

declare(strict_types=1);

namespace Atelier\Pattern\Tile;

use Atelier\Pattern\AbstractPattern;
use Atelier\Pattern\Internal\Guard;
use Atelier\Pattern\Internal\Num;
use Atelier\Pattern\Internal\Wrap;
use Atelier\Svg\Element\Shape\PolygonElement;
use Atelier\Svg\Element\Shape\RectElement;

/**
 * The third regular tessellation, after the square of grid and the hexagon of
 * honeycomb.
 *
 * A strip of triangles alternates one standing on its base with one hanging
 * from the line above. The strip below repeats it half a side across, because
 * the vertices of the two strips have to meet: that offset is what makes the
 * vertical period two strips rather than one.
 *
 * The down opacity paints a full background; up paints the standing triangles
 * over it. Their combined alpha is up + down * (1 - up), so equal intermediate
 * settings do not give a flat tone.
 *
 * The hanging triangles are drawn as one field under the standing ones rather
 * than as their own shapes. Two fills that share an edge leave the hairline
 * every renderer draws between them, and at this many shared edges the tiling
 * would read as ruled. One field carries no seam.
 */
final class Triangles extends AbstractPattern
{
    private function __construct(
        private readonly float $size,
        private readonly float $up,
        private readonly float $down,
    ) {
    }

    /**
     * @param float $size side of one triangle
     * @param float $up   opacity of the standing triangle overlay, 0 to 1
     * @param float $down ink of the field the standing triangles sit on, which is what the hanging ones read as, 0 to 1
     */
    public static function create(float $size = 24.0, float $up = 1.0, float $down = 0.3): self
    {
        Guard::positive($size, 'size');
        Guard::between($up, 0.0, 1.0, 'up');
        Guard::between($down, 0.0, 1.0, 'down');

        return new self($size, $up, $down);
    }

    public function tileWidth(): float
    {
        return $this->size;
    }

    public function tileHeight(): float
    {
        return 2 * $this->rise();
    }

    protected function shapes(): array
    {
        $a = $this->size;
        $h = $this->rise();

        $field = new RectElement();
        $field->setX('0')
            ->setY('0')
            ->setWidth(Num::format($this->tileWidth()))
            ->setHeight(Num::format($this->tileHeight()));
        $field->setAttribute('fill-opacity', Num::format($this->down));

        $shapes = [$this->painted($field)];

        // One standing triangle per strip. Every other one in the plane is a
        // translate of these two.
        $triangles = [
            [[0.0, $h], [$a / 2, 0.0], [$a, $h]],
            [[-$a / 2, 2 * $h], [0.0, $h], [$a / 2, 2 * $h]],
        ];

        foreach ($triangles as $corners) {
            $xs = array_column($corners, 0);
            $ys = array_column($corners, 1);

            $offsets = Wrap::offsets(min($xs), min($ys), max($xs), max($ys), $this->tileWidth(), $this->tileHeight());

            foreach ($offsets as [$dx, $dy]) {
                $triangle = new PolygonElement();
                $triangle->setPoints($this->points($corners, $dx, $dy));
                $triangle->setAttribute('fill-opacity', Num::format($this->up));

                $shapes[] = $this->painted($triangle);
            }
        }

        return $shapes;
    }

    protected function geometry(): array
    {
        return [$this->size, $this->up, $this->down];
    }

    protected function slug(): string
    {
        return 'triangles';
    }

    /**
     * Height of one strip: the height of an equilateral triangle.
     */
    private function rise(): float
    {
        return $this->size * sqrt(3) / 2;
    }

    /**
     * @param list<array{float, float}> $corners
     */
    private function points(array $corners, float $dx, float $dy): string
    {
        $points = [];

        foreach ($corners as [$x, $y]) {
            $points[] = Num::format($x + $dx).','.Num::format($y + $dy);
        }

        return implode(' ', $points);
    }
}
