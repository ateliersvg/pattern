<?php

declare(strict_types=1);

namespace Atelier\Pattern\Tests\Tile;

use Atelier\Pattern\Exception\InvalidArgumentException;
use Atelier\Pattern\Tests\Support\Shapes;
use Atelier\Pattern\Tests\Support\TilingAssertions;
use Atelier\Pattern\Tile\Dots;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

#[CoversClass(Dots::class)]
final class DotsTest extends TestCase
{
    use TilingAssertions;

    public function testTileIsOneCellSquare(): void
    {
        $dots = Dots::create(spacing: 16, radius: 3);

        self::assertSame(16.0, $dots->tileWidth());
        self::assertSame(16.0, $dots->tileHeight());
    }

    public function testTheDotSitsAtTheMiddleOfTheTile(): void
    {
        $circles = Shapes::circles(Dots::create(spacing: 16, radius: 3));

        self::assertSame([['cx' => 8.0, 'cy' => 8.0, 'r' => 3.0]], $circles);
    }

    public function testNothingReachesAnEdge(): void
    {
        $dots = Dots::create(spacing: 16, radius: 8);
        $circle = Shapes::circles($dots)[0];

        self::assertGreaterThanOrEqual(0.0, $circle['cx'] - $circle['r']);
        self::assertGreaterThanOrEqual(0.0, $circle['cy'] - $circle['r']);
        self::assertLessThanOrEqual($dots->tileWidth(), $circle['cx'] + $circle['r']);
        self::assertLessThanOrEqual($dots->tileHeight(), $circle['cy'] + $circle['r']);

        self::assertCirclesAndRectsJoin($dots);
    }

    public function testAStaggeredTileSpansTwoRows(): void
    {
        $dots = Dots::create(spacing: 16, radius: 3, stagger: 0.5);

        self::assertSame(16.0, $dots->tileWidth());
        self::assertSame(32.0, $dots->tileHeight());
    }

    public function testTheShiftedRowIsDrawnAgainstBothVerticalEdges(): void
    {
        $circles = Shapes::circles(Dots::create(spacing: 16, radius: 3, stagger: 0.5));

        self::assertSame([
            ['cx' => 8.0, 'cy' => 8.0, 'r' => 3.0],
            ['cx' => 0.0, 'cy' => 24.0, 'r' => 3.0],
            ['cx' => 16.0, 'cy' => 24.0, 'r' => 3.0],
        ], $circles);
    }

    public function testAStaggerIsReadAsAFractionOfTheSpacing(): void
    {
        $circles = Shapes::circles(Dots::create(spacing: 16, radius: 3, stagger: 0.25));

        self::assertSame([
            ['cx' => 8.0, 'cy' => 8.0, 'r' => 3.0],
            ['cx' => 12.0, 'cy' => 24.0, 'r' => 3.0],
        ], $circles);
    }

    public function testAPhaseOfAHalfPutsTheDotsOnTheNodesOfTheGrid(): void
    {
        $circles = Shapes::circles(Dots::create(spacing: 20, radius: 2, phase: 0.5));

        self::assertSame([
            ['cx' => 0.0, 'cy' => 0.0, 'r' => 2.0],
            ['cx' => 0.0, 'cy' => 20.0, 'r' => 2.0],
            ['cx' => 20.0, 'cy' => 0.0, 'r' => 2.0],
            ['cx' => 20.0, 'cy' => 20.0, 'r' => 2.0],
        ], $circles);
    }

    public function testAPhaseSlidesTheWholeLatticeWithoutChangingTheTile(): void
    {
        $dots = Dots::create(spacing: 20, radius: 2, phase: 0.35);

        self::assertSame(20.0, $dots->tileWidth());
        self::assertSame(20.0, $dots->tileHeight());
        self::assertSame([['cx' => 17.0, 'cy' => 17.0, 'r' => 2.0]], Shapes::circles($dots));
    }

    /**
     * @return iterable<string, array{Dots}>
     */
    public static function lattices(): iterable
    {
        yield 'aligned' => [Dots::create(spacing: 16, radius: 8)];
        yield 'staggered' => [Dots::create(spacing: 16, radius: 8, stagger: 0.5)];
        yield 'staggered by a third' => [Dots::create(spacing: 16, radius: 7, stagger: 1 / 3)];
        yield 'on the grid nodes' => [Dots::create(spacing: 20, radius: 10, phase: 0.5)];
        yield 'phased' => [Dots::create(spacing: 20, radius: 9, phase: 0.8)];
        yield 'staggered and phased' => [Dots::create(spacing: 20, radius: 9, stagger: 0.5, phase: 0.5)];
    }

    #[DataProvider('lattices')]
    public function testEveryDotCrossingAnEdgeHasItsTranslate(Dots $dots): void
    {
        self::assertCirclesAndRectsJoin($dots);
    }

    public function testTwoLatticesAreTwoTiles(): void
    {
        self::assertNotSame(Dots::create()->id(), Dots::create(stagger: 0.5)->id());
        self::assertNotSame(Dots::create()->id(), Dots::create(phase: 0.5)->id());
    }

    public function testDotsWiderThanTheSpacingAreRejected(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('dot diameter must not exceed spacing (16), got 18.');

        Dots::create(spacing: 16, radius: 9);
    }

    public function testSpacingMustBePositive(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('spacing must be a finite number greater than 0');

        Dots::create(spacing: 0);
    }

    public function testRadiusMustBePositive(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('radius must be a finite number greater than 0');

        Dots::create(spacing: 10, radius: -1);
    }

    public function testAStaggerOfAWholePeriodIsRejected(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('stagger must be at least 0 and below 1, got 1.');

        Dots::create(spacing: 16, radius: 3, stagger: 1);
    }

    public function testANegativePhaseIsRejected(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('phase must be at least 0 and below 1, got -0.25.');

        Dots::create(spacing: 16, radius: 3, phase: -0.25);
    }
}
