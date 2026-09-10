<?php

declare(strict_types=1);

namespace Atelier\Pattern\Tests\Internal;

use Atelier\Pattern\Exception\InvalidArgumentException;
use Atelier\Pattern\Internal\Guard;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

#[CoversClass(Guard::class)]
final class GuardTest extends TestCase
{
    public function testPositiveReturnsTheValue(): void
    {
        self::assertSame(3.5, Guard::positive(3.5, 'spacing'));
    }

    /**
     * @return iterable<string, array{float, string}>
     */
    public static function rejectedByPositive(): iterable
    {
        yield 'zero' => [0.0, 'spacing must be a finite number greater than 0, got 0.'];
        yield 'negative' => [-2.0, 'spacing must be a finite number greater than 0, got -2.'];
        yield 'infinite' => [\INF, 'spacing must be a finite number greater than 0, got INF.'];
        yield 'negative infinite' => [-\INF, 'spacing must be a finite number greater than 0, got -INF.'];
        yield 'not a number' => [\NAN, 'spacing must be a finite number greater than 0, got NAN.'];
    }

    #[DataProvider('rejectedByPositive')]
    public function testPositiveRejects(float $value, string $message): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage($message);

        Guard::positive($value, 'spacing');
    }

    public function testAtMostReturnsTheValue(): void
    {
        self::assertSame(4.0, Guard::atMost(4.0, 4.0, 'thickness', 'spacing'));
    }

    public function testAtMostRejectsAValueAboveTheLimit(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('thickness must not exceed spacing (4), got 5.');

        Guard::atMost(5.0, 4.0, 'thickness', 'spacing');
    }

    public function testAtLeastReturnsTheValue(): void
    {
        self::assertSame(2, Guard::atLeast(2, 2, 'cells'));
    }

    public function testAtLeastRejectsACountBelowTheMinimum(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('cells must be at least 2, got 1.');

        Guard::atLeast(1, 2, 'cells');
    }

    public function testBetweenReturnsTheValue(): void
    {
        self::assertSame(0.5, Guard::between(0.5, 0.0, 1.0, 'opacity'));
    }

    /**
     * @return iterable<string, array{float}>
     */
    public static function rejectedByBetween(): iterable
    {
        yield 'below' => [-0.1];
        yield 'above' => [1.1];
        yield 'not a number' => [\NAN];
    }

    #[DataProvider('rejectedByBetween')]
    public function testBetweenRejects(float $value): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('opacity must be between 0 and 1, got ');

        Guard::between($value, 0.0, 1.0, 'opacity');
    }

    public function testFractionReturnsTheValue(): void
    {
        self::assertSame(0.0, Guard::fraction(0.0, 'stagger'));
        self::assertSame(0.999, Guard::fraction(0.999, 'stagger'));
    }

    /**
     * @return iterable<string, array{float, string}>
     */
    public static function rejectedByFraction(): iterable
    {
        yield 'negative' => [-0.25, 'stagger must be at least 0 and below 1, got -0.25.'];
        yield 'one' => [1.0, 'stagger must be at least 0 and below 1, got 1.'];
        yield 'above one' => [1.5, 'stagger must be at least 0 and below 1, got 1.5.'];
        yield 'infinite' => [\INF, 'stagger must be at least 0 and below 1, got INF.'];
        yield 'not a number' => [\NAN, 'stagger must be at least 0 and below 1, got NAN.'];
    }

    #[DataProvider('rejectedByFraction')]
    public function testFractionRejects(float $value, string $message): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage($message);

        Guard::fraction($value, 'stagger');
    }

    public function testFiniteReturnsTheValue(): void
    {
        self::assertSame(-30.0, Guard::finite(-30.0, 'angle'));
    }

    public function testFiniteRejectsANonNumber(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('angle must be a finite number, got NAN.');

        Guard::finite(\NAN, 'angle');
    }

    public function testNotBlankReturnsTheValue(): void
    {
        self::assertSame('#c0392b', Guard::notBlank('#c0392b', 'color'));
    }

    public function testNotBlankRejectsWhitespace(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('color must not be blank.');

        Guard::notBlank("\t ", 'color');
    }
}
