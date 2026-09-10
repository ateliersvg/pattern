<?php

declare(strict_types=1);

namespace Atelier\Pattern\Internal;

use Atelier\Pattern\Exception\InvalidArgumentException;

/**
 * Geometry preconditions shared by every tile.
 *
 * A tile that joins with itself only does so within a range of values. These
 * checks state that range instead of leaving the caller with a broken tiling.
 *
 * @internal
 */
final class Guard
{
    /**
     * @throws InvalidArgumentException if the value is not a finite number above zero
     */
    public static function positive(float $value, string $name): float
    {
        if (!is_finite($value) || $value <= 0.0) {
            throw new InvalidArgumentException(\sprintf('%s must be a finite number greater than 0, got %s.', $name, self::describe($value)));
        }

        return $value;
    }

    /**
     * @throws InvalidArgumentException if the value exceeds the limit
     */
    public static function atMost(float $value, float $limit, string $name, string $limitLabel): float
    {
        if ($value > $limit) {
            throw new InvalidArgumentException(\sprintf('%s must not exceed %s (%s), got %s.', $name, $limitLabel, self::describe($limit), self::describe($value)));
        }

        return $value;
    }

    /**
     * @throws InvalidArgumentException if the count is below the minimum
     */
    public static function atLeast(int $value, int $minimum, string $name): int
    {
        if ($value < $minimum) {
            throw new InvalidArgumentException(\sprintf('%s must be at least %d, got %d.', $name, $minimum, $value));
        }

        return $value;
    }

    /**
     * @throws InvalidArgumentException if the value is outside the closed range
     */
    public static function between(float $value, float $min, float $max, string $name): float
    {
        if (!is_finite($value) || $value < $min || $value > $max) {
            throw new InvalidArgumentException(\sprintf('%s must be between %s and %s, got %s.', $name, self::describe($min), self::describe($max), self::describe($value)));
        }

        return $value;
    }

    /**
     * @throws InvalidArgumentException if the value is outside the half open range [0, 1)
     */
    public static function fraction(float $value, string $name): float
    {
        if (!is_finite($value) || $value < 0.0 || $value >= 1.0) {
            throw new InvalidArgumentException(\sprintf('%s must be at least 0 and below 1, got %s.', $name, self::describe($value)));
        }

        return $value;
    }

    /**
     * @throws InvalidArgumentException if the value is not a finite number
     */
    public static function finite(float $value, string $name): float
    {
        if (!is_finite($value)) {
            throw new InvalidArgumentException(\sprintf('%s must be a finite number, got %s.', $name, self::describe($value)));
        }

        return $value;
    }

    /**
     * @throws InvalidArgumentException if the string is empty or blank
     */
    public static function notBlank(string $value, string $name): string
    {
        if ('' === trim($value)) {
            throw new InvalidArgumentException(\sprintf('%s must not be blank.', $name));
        }

        return $value;
    }

    private static function describe(float $value): string
    {
        if (is_nan($value)) {
            return 'NAN';
        }

        if (!is_finite($value)) {
            return $value > 0 ? 'INF' : '-INF';
        }

        return Num::format($value);
    }
}
