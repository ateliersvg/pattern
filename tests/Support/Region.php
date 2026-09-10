<?php

declare(strict_types=1);

namespace Atelier\Pattern\Tests\Support;

/**
 * Plane geometry for the tiles that pave rather than draw.
 *
 * A pavement is right when every point of the tile falls in exactly one shape:
 * one is a gap, two is an overlap. These are the two measurements that state it.
 */
final class Region
{
    /**
     * Whether a point falls inside a polygon, by ray casting. A point sitting
     * exactly on an edge is undefined, so sample away from the vertices.
     *
     * @param list<array{0: float, 1: float}> $polygon
     */
    public static function contains(array $polygon, float $x, float $y): bool
    {
        $inside = false;
        $corners = \count($polygon);

        for ($corner = 0, $previous = $corners - 1; $corner < $corners; $previous = $corner++) {
            [$x1, $y1] = $polygon[$corner];
            [$x2, $y2] = $polygon[$previous];

            if (($y1 > $y) !== ($y2 > $y) && $x < ($x2 - $x1) * ($y - $y1) / ($y2 - $y1) + $x1) {
                $inside = !$inside;
            }
        }

        return $inside;
    }

    /**
     * How many of these polygons hold the point.
     *
     * @param list<list<array{0: float, 1: float}>> $polygons
     */
    public static function cover(array $polygons, float $x, float $y): int
    {
        $holding = 0;

        foreach ($polygons as $polygon) {
            if (self::contains($polygon, $x, $y)) {
                ++$holding;
            }
        }

        return $holding;
    }

    /**
     * The area a polygon encloses, whichever way round it is written.
     *
     * @param list<array{0: float, 1: float}> $polygon
     */
    public static function area(array $polygon): float
    {
        $twice = 0.0;
        $corners = \count($polygon);

        for ($corner = 0; $corner < $corners; ++$corner) {
            [$x1, $y1] = $polygon[$corner];
            [$x2, $y2] = $polygon[($corner + 1) % $corners];

            $twice += $x1 * $y2 - $x2 * $y1;
        }

        return abs($twice) / 2;
    }
}
