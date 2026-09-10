<?php

declare(strict_types=1);

namespace Atelier\Pattern\Tests\Internal;

use Atelier\Pattern\Internal\Random;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(Random::class)]
final class RandomTest extends TestCase
{
    public function testTheSameSeedDrawsTheSameNumbers(): void
    {
        $first = new Random(7);
        $second = new Random(7);

        $drawn = [];
        $again = [];

        for ($i = 0; $i < 20; ++$i) {
            $drawn[] = $first->float();
            $again[] = $second->float();
        }

        self::assertSame($drawn, $again);
    }

    public function testTwoSeedsDrawTwoSequences(): void
    {
        $first = new Random(1);
        $second = new Random(2);

        $shared = 0;

        for ($i = 0; $i < 20; ++$i) {
            if ($first->float() === $second->float()) {
                ++$shared;
            }
        }

        self::assertSame(0, $shared);
    }

    public function testTheHighBitsOfTheSeedAreNotThrownAway(): void
    {
        self::assertNotSame((new Random(1))->float(), (new Random(1 + (1 << 32)))->float());
    }

    public function testTheSeedThatWouldEmptyTheStateStillDraws(): void
    {
        // This value cancels the constant the seed is mixed with. Left alone
        // the state would be zero, and a xorshift never leaves zero.
        $random = new Random(0x9E3779B9);

        $drawn = [];

        for ($i = 0; $i < 10; ++$i) {
            $drawn[] = $random->float();
        }

        self::assertGreaterThan(1, \count(array_unique($drawn)));
    }

    public function testFloatsCoverTheUnitIntervalWithoutLeavingIt(): void
    {
        $random = new Random(3);

        $drawn = [];

        for ($i = 0; $i < 1000; ++$i) {
            $drawn[] = $random->float();
        }

        self::assertGreaterThanOrEqual(0.0, min($drawn));
        self::assertLessThan(1.0, max($drawn));
        self::assertLessThan(0.05, min($drawn));
        self::assertGreaterThan(0.95, max($drawn));
    }

    public function testBetweenCoversItsRangeWithoutLeavingIt(): void
    {
        $random = new Random(5);

        $drawn = [];

        for ($i = 0; $i < 1000; ++$i) {
            $drawn[] = $random->between(-3.0, 7.0);
        }

        self::assertGreaterThanOrEqual(-3.0, min($drawn));
        self::assertLessThan(7.0, max($drawn));
        self::assertLessThan(-2.9, min($drawn));
        self::assertGreaterThan(6.9, max($drawn));
    }

    public function testAnEmptyRangeGivesItsBound(): void
    {
        self::assertSame(4.0, (new Random(5))->between(4.0, 4.0));
    }

    public function testIntegersCoverTheirRangeAndNothingElse(): void
    {
        $random = new Random(9);

        $seen = [];

        for ($i = 0; $i < 1000; ++$i) {
            $seen[$random->int(3, 5)] = true;
        }

        ksort($seen);

        self::assertSame([3, 4, 5], array_keys($seen));
    }

    public function testARangeOfOneAlwaysGivesThatValue(): void
    {
        $random = new Random(11);

        $seen = [];

        for ($i = 0; $i < 50; ++$i) {
            $seen[$random->int(2, 2)] = true;
        }

        self::assertSame([2], array_keys($seen));
    }
}
