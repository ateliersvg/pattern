<?php

declare(strict_types=1);

namespace Atelier\Pattern\Tests\Tile;

use Atelier\Pattern\Exception\InvalidArgumentException;
use Atelier\Pattern\Tests\Support\Shapes;
use Atelier\Pattern\Tests\Support\TilingAssertions;
use Atelier\Pattern\Tile\JitteredDots;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(JitteredDots::class)]
final class JitteredDotsTest extends TestCase
{
    use TilingAssertions;

    private const float TOLERANCE = 0.0001;

    public function testTheTileHoldsAsManyCellsAsAsked(): void
    {
        $dots = JitteredDots::create(spacing: 16, radius: 2.4, jitter: 0.3, cells: 5, seed: 1);

        self::assertSame(80.0, $dots->tileWidth());
        self::assertSame(80.0, $dots->tileHeight());
    }

    public function testEveryDotStaysWithinItsJitterOfALatticeNodeOnBothAxes(): void
    {
        $spacing = 16.0;
        $jitter = 0.5;
        $reach = $jitter * $spacing;

        foreach (Shapes::circles(JitteredDots::create(spacing: $spacing, radius: 2.4, jitter: $jitter, cells: 6, seed: 1)) as $dot) {
            $node = [round($dot['cx'] / $spacing) * $spacing, round($dot['cy'] / $spacing) * $spacing];

            self::assertLessThanOrEqual(
                $reach + self::TOLERANCE,
                abs($dot['cx'] - $node[0]),
                \sprintf('The dot at (%s, %s) left its node across by more than the jitter allows.', $dot['cx'], $dot['cy']),
            );
            self::assertLessThanOrEqual(
                $reach + self::TOLERANCE,
                abs($dot['cy'] - $node[1]),
                \sprintf('The dot at (%s, %s) left its node down by more than the jitter allows.', $dot['cx'], $dot['cy']),
            );
        }
    }

    /**
     * The two axes are drawn apart, so a dot lands anywhere in its square rather
     * than piling up near its node: that is what stops the lattice showing
     * through. Sampled polar with a flat radius it would crowd the middle.
     */
    public function testTheDotsSpreadOverTheirSquareRatherThanCrowdingTheirNode(): void
    {
        $spacing = 16.0;
        $reach = 0.5 * $spacing;

        $far = 0;
        $dots = Shapes::circles(JitteredDots::create(spacing: $spacing, radius: 2.4, jitter: 0.5, cells: 8, seed: 1));

        foreach ($dots as $dot) {
            $node = [round($dot['cx'] / $spacing) * $spacing, round($dot['cy'] / $spacing) * $spacing];

            if (max(abs($dot['cx'] - $node[0]), abs($dot['cy'] - $node[1])) > 0.5 * $reach) {
                ++$far;
            }
        }

        // Three quarters of a square lies outside the half that is nearest the
        // node. A tenth either side leaves room for the draw.
        self::assertGreaterThan(0.65 * \count($dots), $far);
    }

    public function testTheDotsOnTheEdgesAreDrawnTwice(): void
    {
        $cells = 6;
        $circles = Shapes::circles(JitteredDots::create(spacing: 16, radius: 2.4, jitter: 0.5, cells: $cells, seed: 1));

        self::assertGreaterThan($cells * $cells, \count($circles), 'No dot was repeated, so nothing joins across the seam.');
    }

    public function testEveryDotCrossingAnEdgeHasItsTranslate(): void
    {
        self::assertCirclesAndRectsJoin(JitteredDots::create(spacing: 16, radius: 2.4, jitter: 0.5, cells: 6, seed: 1));
    }

    public function testADotOnTheCornerNodeIsDrawnOnAllFourCorners(): void
    {
        // Seed 9 pushes the dot on the corner node up and to the left, so it
        // overhangs two edges at once.
        $dots = JitteredDots::create(spacing: 16, radius: 2.4, jitter: 0.5, cells: 6, seed: 9);
        $circles = Shapes::circles($dots);

        $corners = array_values(array_filter(
            $circles,
            static fn (array $dot): bool => $dot['cx'] < 0.0 && $dot['cy'] < 0.0,
        ));

        self::assertCount(1, $corners);

        foreach ([[$dots->tileWidth(), 0.0], [0.0, $dots->tileHeight()], [$dots->tileWidth(), $dots->tileHeight()]] as [$dx, $dy]) {
            self::assertTrue(
                $this->hasDot($circles, $corners[0]['cx'] + $dx, $corners[0]['cy'] + $dy),
                \sprintf('The dot on the corner is missing its copy at (%s, %s).', $corners[0]['cx'] + $dx, $corners[0]['cy'] + $dy),
            );
        }
    }

    public function testTheTileStillJoinsAtTheLimitsOfItsGuards(): void
    {
        self::assertCirclesAndRectsJoin(JitteredDots::create(spacing: 16, radius: 8, jitter: 1, cells: 2, seed: 4));
    }

    public function testTheSameSeedDrawsTheSameTile(): void
    {
        $first = JitteredDots::create(seed: 12);
        $second = JitteredDots::create(seed: 12);

        self::assertSame(Shapes::circles($first), Shapes::circles($second));
        self::assertSame($first->id(), $second->id());
    }

    public function testTwoSeedsDrawTwoTiles(): void
    {
        $first = JitteredDots::create(seed: 12);
        $second = JitteredDots::create(seed: 13);

        self::assertNotSame(Shapes::circles($first), Shapes::circles($second));
        self::assertNotSame($first->id(), $second->id());
    }

    public function testDotsWiderThanTheSpacingAreRejected(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('dot diameter must not exceed spacing (16), got 20.');

        JitteredDots::create(spacing: 16, radius: 10);
    }

    public function testSpacingMustBePositive(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('spacing must be a finite number greater than 0');

        JitteredDots::create(spacing: -4);
    }

    public function testRadiusMustBePositive(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('radius must be a finite number greater than 0');

        JitteredDots::create(spacing: 16, radius: 0);
    }

    public function testAJitterReachingPastANeighbouringNodeIsRejected(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('jitter must be between 0 and 1, got 1.2.');

        JitteredDots::create(spacing: 16, radius: 2.4, jitter: 1.2);
    }

    public function testANegativeJitterIsRejected(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('jitter must be between 0 and 1, got -0.1.');

        JitteredDots::create(spacing: 16, radius: 2.4, jitter: -0.1);
    }

    public function testATileOfOneCellIsRejected(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('cells must be at least 2, got 0.');

        JitteredDots::create(spacing: 16, radius: 2.4, jitter: 0.5, cells: 0);
    }

    /**
     * @param list<array{cx: float, cy: float, r: float}> $circles
     */
    private function hasDot(array $circles, float $cx, float $cy): bool
    {
        foreach ($circles as $dot) {
            if (abs($dot['cx'] - $cx) < self::TOLERANCE && abs($dot['cy'] - $cy) < self::TOLERANCE) {
                return true;
            }
        }

        return false;
    }
}
