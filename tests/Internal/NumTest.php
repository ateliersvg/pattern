<?php

declare(strict_types=1);

namespace Atelier\Pattern\Tests\Internal;

use Atelier\Pattern\Internal\Num;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

#[CoversClass(Num::class)]
final class NumTest extends TestCase
{
    /**
     * @return iterable<string, array{float, string}>
     */
    public static function values(): iterable
    {
        yield 'integer' => [12.0, '12'];
        yield 'zero' => [0.0, '0'];
        yield 'trailing zeros dropped' => [2.5000, '2.5'];
        yield 'kept to four decimals' => [24.248711, '24.2487'];
        yield 'rounded up' => [0.00005, '0.0001'];
        yield 'negative' => [-7.25, '-7.25'];
        yield 'negative zero collapsed' => [-0.000001, '0'];
    }

    #[DataProvider('values')]
    public function testItFormatsCompactly(float $value, string $expected): void
    {
        self::assertSame($expected, Num::format($value));
    }

    public function testItRoundsToTheOutputPrecision(): void
    {
        self::assertSame(24.2487, Num::round(24.24871130596428));
        self::assertSame(1.0, Num::round(1.0));
    }
}
