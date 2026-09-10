<?php

declare(strict_types=1);

namespace Atelier\Pattern\Internal;

/**
 * The pseudo-random source the irregular tiles draw from.
 *
 * The state is local to the instance, so a tile always reads the same numbers
 * in the same order whatever else the process does. Nothing global is touched:
 * rand(), mt_rand() and random_int() would make the drawing depend on how many
 * numbers were consumed before.
 *
 * The only promise made to the caller is that the same seed and the same
 * version give the same drawing. The generator below is a 32 bit xorshift; the
 * sequence it produces is not part of any contract.
 *
 * @internal
 */
final class Random
{
    /** Kept away from zero: xorshift stays on zero once it reaches it. */
    private const int FALLBACK_STATE = 0x1F123BB5;

    private const int MASK = 0xFFFFFFFF;

    private int $state;

    public function __construct(int $seed)
    {
        // Both halves of the seed are folded in, so two seeds differing only in
        // their high bits still draw two different tiles.
        $state = ($seed ^ ($seed >> 32) ^ 0x9E3779B9) & self::MASK;

        $this->state = 0 !== $state ? $state : self::FALLBACK_STATE;

        // Nearby seeds start on nearby states; a few steps pull them apart.
        for ($i = 0; $i < 8; ++$i) {
            $this->next();
        }
    }

    /**
     * A number in [0, 1).
     */
    public function float(): float
    {
        return $this->next() / 4294967296.0;
    }

    /**
     * A number in [$min, $max).
     */
    public function between(float $min, float $max): float
    {
        return $min + ($max - $min) * $this->float();
    }

    /**
     * An integer in [$min, $max], both ends included. $min must not exceed $max.
     */
    public function int(int $min, int $max): int
    {
        return $min + (int) ($this->float() * ($max - $min + 1));
    }

    private function next(): int
    {
        $state = $this->state;
        $state ^= ($state << 13) & self::MASK;
        $state ^= $state >> 17;
        $state ^= ($state << 5) & self::MASK;

        return $this->state = $state;
    }
}
