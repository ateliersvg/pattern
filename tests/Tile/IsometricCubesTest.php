<?php

declare(strict_types=1);

namespace Atelier\Pattern\Tests\Tile;

use Atelier\Pattern\Exception\InvalidArgumentException;
use Atelier\Pattern\Tests\Support\Region;
use Atelier\Pattern\Tests\Support\Shapes;
use Atelier\Pattern\Tests\Support\TilingAssertions;
use Atelier\Pattern\Tile\IsometricCubes;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(IsometricCubes::class)]
final class IsometricCubesTest extends TestCase
{
    use TilingAssertions;

    /** Samples across the tile, per axis. Prime, and read off centre, so none lands on an edge of a face. */
    private const int SAMPLES = 41;

    private const float OFFSET = 0.3;

    public function testTheTileIsTheOneOfTheHexagonalLattice(): void
    {
        $cubes = IsometricCubes::create(size: 20);

        self::assertEqualsWithDelta(sqrt(3) * 20, $cubes->tileWidth(), 0.0001);
        self::assertSame(60.0, $cubes->tileHeight());
    }

    public function testEachHexagonIsCutIntoThreeRhombiOfTheCubeEdge(): void
    {
        $size = 22.0;
        $faces = Shapes::polygons(IsometricCubes::create(size: $size));

        self::assertCount(15, $faces);

        foreach ($faces as $face) {
            self::assertCount(4, $face);

            foreach ([[0, 1], [1, 2], [2, 3], [3, 0]] as [$from, $to]) {
                self::assertEqualsWithDelta($size, hypot($face[$to][0] - $face[$from][0], $face[$to][1] - $face[$from][1]), 0.0001);
            }
        }
    }

    public function testTheFacesPaveTheTileWithoutGapOrOverlap(): void
    {
        $cubes = IsometricCubes::create(size: 22);
        $faces = Shapes::polygons($cubes);

        $gaps = 0;
        $overlaps = 0;

        for ($column = 0; $column < self::SAMPLES; ++$column) {
            for ($row = 0; $row < self::SAMPLES; ++$row) {
                $holding = Region::cover(
                    $faces,
                    ($column + self::OFFSET) * $cubes->tileWidth() / self::SAMPLES,
                    ($row + self::OFFSET) * $cubes->tileHeight() / self::SAMPLES,
                );

                $gaps += $holding < 1 ? 1 : 0;
                $overlaps += $holding > 1 ? 1 : 0;
            }
        }

        self::assertSame(0, $gaps, 'The faces leave a point of the tile uncovered.');
        self::assertSame(0, $overlaps, 'Two faces hold the same point of the tile.');
    }

    public function testEveryFaceCrossingAnEdgeHasItsTranslate(): void
    {
        $cubes = IsometricCubes::create(size: 22);
        $faces = Shapes::polygons($cubes);

        self::assertGreaterThan(0, self::countEdgeCrossings($faces, $cubes->tileWidth(), $cubes->tileHeight()));
        self::assertPolygonsJoin($cubes);
    }

    public function testTheThreeFacesOfACubeStandWhereTheirInkSaysTheyDo(): void
    {
        $cubes = IsometricCubes::create(size: 22, topFace: 0.3, leftFace: 0.85, rightFace: 0.55);

        foreach (self::cubesOf($cubes) as $cube) {
            self::assertSame(['0.3', '0.55', '0.85'], self::sortedInks($cube));

            $top = self::faceWithInk($cube, '0.3');
            $left = self::faceWithInk($cube, '0.85');
            $right = self::faceWithInk($cube, '0.55');

            // The middle of the hexagon is the first point of every face.
            [$cx, $cy] = $top['points'][0];

            self::assertLessThan($cy, self::middleOf($top['points'])[1], 'The flat face is not the one on top.');
            self::assertLessThan($cx, self::middleOf($left['points'])[0], 'The left face is not the one on the left.');
            self::assertGreaterThan($cx, self::middleOf($right['points'])[0], 'The right face is not the one on the right.');
        }
    }

    public function testTheFacesAreFilledNotOutlined(): void
    {
        foreach (Shapes::of(IsometricCubes::create(size: 22)) as $face) {
            self::assertSame('currentColor', $face->getAttribute('fill'));
            self::assertNull($face->getAttribute('stroke'));
        }
    }

    public function testSizeMustBePositive(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('size must be a finite number greater than 0, got 0.');

        IsometricCubes::create(size: 0);
    }

    public function testTheTopFaceInkMustBeAnOpacity(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('topFace must be between 0 and 1, got 1.2.');

        IsometricCubes::create(size: 22, topFace: 1.2);
    }

    public function testTheLeftFaceInkMustBeAnOpacity(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('leftFace must be between 0 and 1, got -0.1.');

        IsometricCubes::create(size: 22, leftFace: -0.1);
    }

    public function testTheRightFaceInkMustBeAnOpacity(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('rightFace must be between 0 and 1, got 2.');

        IsometricCubes::create(size: 22, rightFace: 2);
    }

    /**
     * The faces, three by three, one group per hexagon.
     *
     * @return list<list<array{points: list<array{0: float, 1: float}>, ink: string}>>
     */
    private static function cubesOf(IsometricCubes $cubes): array
    {
        $points = Shapes::polygons($cubes);
        $faces = [];

        foreach (Shapes::of($cubes) as $index => $shape) {
            $faces[intdiv($index, 3)][] = [
                'points' => $points[$index],
                'ink' => (string) $shape->getAttribute('fill-opacity'),
            ];
        }

        return array_values(array_map(array_values(...), $faces));
    }

    /**
     * @param list<array{points: list<array{0: float, 1: float}>, ink: string}> $cube
     *
     * @return list<string>
     */
    private static function sortedInks(array $cube): array
    {
        $inks = array_column($cube, 'ink');
        sort($inks);

        return $inks;
    }

    /**
     * @param list<array{points: list<array{0: float, 1: float}>, ink: string}> $cube
     *
     * @return array{points: list<array{0: float, 1: float}>, ink: string}
     */
    private static function faceWithInk(array $cube, string $ink): array
    {
        foreach ($cube as $face) {
            if ($ink === $face['ink']) {
                return $face;
            }
        }

        self::fail(sprintf('No face carries the ink %s.', $ink));
    }

    /**
     * @param list<array{0: float, 1: float}> $points
     *
     * @return array{0: float, 1: float}
     */
    private static function middleOf(array $points): array
    {
        return [
            array_sum(array_column($points, 0)) / \count($points),
            array_sum(array_column($points, 1)) / \count($points),
        ];
    }
}
