<?php

declare(strict_types=1);

namespace Atelier\Pattern\Internal;

/**
 * Number formatting shared by every tile.
 *
 * @internal
 */
final class Num
{
    /** Decimals kept in attribute output. Enough for sqrt(3) tile widths. */
    public const int PRECISION = 4;

    /**
     * Formats a coordinate as a compact attribute value: fixed precision,
     * trailing zeros removed, no negative zero.
     */
    public static function format(float $value): string
    {
        $formatted = rtrim(rtrim(number_format($value, self::PRECISION, '.', ''), '0'), '.');

        return '-0' === $formatted ? '0' : $formatted;
    }

    /**
     * Rounds to the output precision, so geometry assertions compare the same
     * values the renderer sees.
     */
    public static function round(float $value): float
    {
        return round($value, self::PRECISION);
    }
}
