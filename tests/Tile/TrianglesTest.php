<?php

declare(strict_types=1);

namespace Atelier\Pattern\Tests\Tile;

use Atelier\Pattern\Exception\InvalidArgumentException;
use Atelier\Pattern\Tests\Support\Shapes;
use Atelier\Pattern\Tests\Support\TilingAssertions;
use Atelier\Pattern\Tile\Triangles;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(Triangles::class)]
final class TrianglesTest extends TestCase
{
    use TilingAssertions;

    public function testTheTileIsOneSideByTwoStrips(): void
    {
        $triangles = Triangles::create(size: 24);

        self::assertSame(24.0, $triangles->tileWidth());
        self::assertEqualsWithDelta(24 * sqrt(3), $triangles->tileHeight(), 0.0001);
    }

    public function testTheHangingTrianglesAreOneFieldRatherThanTheirOwnShapes(): void
    {
        $triangles = Triangles::create(size: 24, up: 0.9, down: 0.35);
        $rects = Shapes::rects($triangles);

        self::assertCount(1, $rects, 'One field, whatever the standing triangles do.');
        self::assertSame(0.0, $rects[0]['x']);
        self::assertSame(0.0, $rects[0]['y']);
        self::assertSame($triangles->tileWidth(), $rects[0]['width']);
        self::assertEqualsWithDelta($triangles->tileHeight(), $rects[0]['height'], 0.0001);
    }

    public function testEachStripCarriesOneStandingTriangle(): void
    {
        $centres = [];

        foreach (Shapes::polygons(Triangles::create(size: 24)) as $triangle) {
            $centres[] = array_sum(array_column($triangle, 1)) / 3;
        }

        // Two strips inside the tile, plus the copies the edges call for.
        self::assertGreaterThanOrEqual(2, \count($centres));
    }

    public function testEveryTriangleIsEquilateralOnTheSizeItWasGiven(): void
    {
        foreach (Shapes::polygons(Triangles::create(size: 30)) as $triangle) {
            self::assertCount(3, $triangle);

            foreach ([[0, 1], [1, 2], [2, 0]] as [$from, $to]) {
                $side = hypot(
                    $triangle[$to][0] - $triangle[$from][0],
                    $triangle[$to][1] - $triangle[$from][1],
                );

                self::assertEqualsWithDelta(30.0, $side, 0.0001);
            }
        }
    }

    public function testTheBackgroundIsPaintedBeforeTheStandingTriangleOverlay(): void
    {
        $shapes = Shapes::of(Triangles::create(size: 24, up: 0.8, down: 0.25));

        self::assertSame('0.25', $shapes[0]->getAttribute('fill-opacity'));
        self::assertSame('0.8', $shapes[1]->getAttribute('fill-opacity'));
    }

    public function testEveryTriangleCrossingAnEdgeHasItsTranslate(): void
    {
        self::assertPolygonsJoin(Triangles::create(size: 24));
    }

    public function testSizeMustBePositive(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('size must be a finite number greater than 0, got 0.');

        Triangles::create(size: 0);
    }

    public function testAnInkOutsideTheRangeIsRejected(): void
    {
        $this->expectException(InvalidArgumentException::class);

        Triangles::create(size: 24, down: 1.4);
    }
}
