<?php

declare(strict_types=1);

namespace Atelier\Pattern\Internal;

/**
 * The hexagonal lattice the hexagon tiles are laid out on.
 *
 * A hexagon of radius r standing on a point is sqrt(3)*r wide and 2*r tall.
 * Centres sit sqrt(3)*r apart across and 1.5*r apart down, every other row
 * shifted by half a width. The smallest repeating tile is therefore sqrt(3)*r
 * by 3*r and holds two rows.
 *
 * @internal
 */
final class Hex
{
    /**
     * Width of the smallest tile a lattice of this radius repeats on.
     */
    public static function tileWidth(float $radius): float
    {
        return sqrt(3) * $radius;
    }

    /**
     * Height of the smallest tile a lattice of this radius repeats on.
     */
    public static function tileHeight(float $radius): float
    {
        return 3 * $radius;
    }

    /**
     * Every lattice centre inside the tile, edges included.
     *
     * The four on the corners are translates of one another by a full period,
     * so the clip on one is filled by another; the fifth stands in the middle
     * and its flat sides land on the vertical edges.
     *
     * @return list<array{float, float}>
     */
    public static function centres(float $radius): array
    {
        $width = self::tileWidth($radius);
        $height = self::tileHeight($radius);

        return [
            [0.0, 0.0],
            [$width, 0.0],
            [$width / 2, $height / 2],
            [0.0, $height],
            [$width, $height],
        ];
    }

    /**
     * The six vertices of a hexagon standing on a point, from the top vertex
     * clockwise.
     *
     * @return array{array{float, float}, array{float, float}, array{float, float}, array{float, float}, array{float, float}, array{float, float}}
     */
    public static function vertices(float $cx, float $cy, float $radius): array
    {
        $rise = $radius / 2;
        $reach = self::tileWidth($radius) / 2;

        return [
            [$cx, $cy - $radius],
            [$cx + $reach, $cy - $rise],
            [$cx + $reach, $cy + $rise],
            [$cx, $cy + $radius],
            [$cx - $reach, $cy + $rise],
            [$cx - $reach, $cy - $rise],
        ];
    }
}
