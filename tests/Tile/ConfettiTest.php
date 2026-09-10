<?php

declare(strict_types=1);

namespace Atelier\Pattern\Tests\Tile;

use Atelier\Pattern\Exception\InvalidArgumentException;
use Atelier\Pattern\Tests\Support\Shapes;
use Atelier\Pattern\Tests\Support\TilingAssertions;
use Atelier\Pattern\Tile\Confetti;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(Confetti::class)]
final class ConfettiTest extends TestCase
{
    use TilingAssertions;

    private const float TOLERANCE = 0.0001;

    public function testTheTileHoldsAsManyCellsAsAsked(): void
    {
        $confetti = Confetti::create(spacing: 20, size: 4, cells: 4, seed: 1);

        self::assertSame(80.0, $confetti->tileWidth());
        self::assertSame(80.0, $confetti->tileHeight());
    }

    public function testEveryShapeHasThreeToFiveSides(): void
    {
        foreach (Shapes::polygons(Confetti::create(spacing: 20, size: 4, cells: 5, seed: 1)) as $shape) {
            self::assertGreaterThanOrEqual(3, \count($shape));
            self::assertLessThanOrEqual(5, \count($shape));
        }
    }

    public function testNoVertexReachesFurtherThanTheSizeAsked(): void
    {
        $size = 4.0;

        foreach (Shapes::polygons(Confetti::create(spacing: 20, size: $size, cells: 5, seed: 1)) as $shape) {
            $cx = array_sum(array_column($shape, 0)) / \count($shape);
            $cy = array_sum(array_column($shape, 1)) / \count($shape);

            foreach ($shape as [$x, $y]) {
                $reach = hypot($x - $cx, $y - $cy);

                self::assertLessThanOrEqual($size + self::TOLERANCE, $reach);
                self::assertGreaterThan(0.5 * $size, $reach, 'A shape is never dealt less than half the size asked.');
            }
        }
    }

    public function testTheShapesOnTheEdgesAreDrawnTwice(): void
    {
        $cells = 5;
        $polygons = Shapes::polygons(Confetti::create(spacing: 20, size: 4, cells: $cells, seed: 1));

        self::assertGreaterThan($cells * $cells, \count($polygons), 'No shape was repeated, so nothing joins across the seam.');
    }

    public function testEveryShapeCrossingAnEdgeHasItsTranslate(): void
    {
        $confetti = Confetti::create(spacing: 20, size: 4, cells: 5, seed: 1);

        self::assertGreaterThan(
            0,
            self::countEdgeCrossings(Shapes::polygons($confetti), $confetti->tileWidth(), $confetti->tileHeight()),
            'No shape crosses an edge, so nothing was checked.',
        );

        self::assertPolygonsJoin($confetti);
    }

    public function testTheTileStillJoinsAtTheLimitsOfItsGuards(): void
    {
        self::assertPolygonsJoin(Confetti::create(spacing: 20, size: 10, cells: 2, seed: 7));
    }

    public function testTheShapesArePainted(): void
    {
        foreach (Shapes::of(Confetti::create(spacing: 20, size: 4, cells: 3, seed: 1)) as $shape) {
            self::assertSame('currentColor', $shape->getAttribute('fill'));
            self::assertNull($shape->getAttribute('stroke'));
        }
    }

    public function testTheSameSeedDrawsTheSameTile(): void
    {
        $first = Confetti::create(seed: 12);
        $second = Confetti::create(seed: 12);

        self::assertSame(Shapes::polygons($first), Shapes::polygons($second));
        self::assertSame($first->id(), $second->id());
    }

    public function testTwoSeedsDrawTwoTiles(): void
    {
        $first = Confetti::create(seed: 12);
        $second = Confetti::create(seed: 13);

        self::assertNotSame(Shapes::polygons($first), Shapes::polygons($second));
        self::assertNotSame($first->id(), $second->id());
    }

    public function testShapesWiderThanTheSpacingAreRejected(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('shape diameter must not exceed spacing (20), got 24.');

        Confetti::create(spacing: 20, size: 12);
    }

    public function testSpacingMustBePositive(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('spacing must be a finite number greater than 0');

        Confetti::create(spacing: 0);
    }

    public function testSizeMustBePositive(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('size must be a finite number greater than 0, got NAN.');

        Confetti::create(spacing: 20, size: \NAN);
    }

    public function testATileOfOneCellIsRejected(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('cells must be at least 2, got 1.');

        Confetti::create(spacing: 20, size: 4, cells: 1);
    }
}
