<?php

declare(strict_types=1);

namespace Atelier\Pattern\Tests\Tile;

use Atelier\Pattern\Exception\InvalidArgumentException;
use Atelier\Pattern\Tests\Support\Region;
use Atelier\Pattern\Tests\Support\Shapes;
use Atelier\Pattern\Tests\Support\TilingAssertions;
use Atelier\Pattern\Tile\Voronoi;
use Atelier\Svg\Element\ElementInterface;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

#[CoversClass(Voronoi::class)]
final class VoronoiTest extends TestCase
{
    use TilingAssertions;

    /** Samples across the tile, per axis. Odd, so nothing lands on the middle. */
    private const int SAMPLES = 41;

    public function testTheTileIsTheSquareItWasGiven(): void
    {
        $voronoi = Voronoi::create(size: 90, thickness: 1, sites: 8);

        self::assertSame(90.0, $voronoi->tileWidth());
        self::assertSame(90.0, $voronoi->tileHeight());
    }

    /**
     * @return iterable<string, array{int}>
     */
    public static function seeds(): iterable
    {
        yield 'seed 1' => [1];
        yield 'seed 7' => [7];
        yield 'seed 42' => [42];
    }

    #[DataProvider('seeds')]
    public function testTheCellsPaveTheTileWithoutGapOrOverlap(int $seed): void
    {
        $voronoi = Voronoi::create(size: 120, thickness: 1.4, sites: 14, seed: $seed);
        $cells = Shapes::polygons($voronoi);
        $size = $voronoi->tileWidth();

        $gaps = 0;
        $overlaps = 0;

        for ($column = 0; $column < self::SAMPLES; ++$column) {
            for ($row = 0; $row < self::SAMPLES; ++$row) {
                $holding = Region::cover($cells, ($column + 0.5) * $size / self::SAMPLES, ($row + 0.5) * $size / self::SAMPLES);

                $gaps += $holding < 1 ? 1 : 0;
                $overlaps += $holding > 1 ? 1 : 0;
            }
        }

        self::assertSame(0, $gaps, 'The cells leave a point of the tile uncovered.');
        self::assertSame(0, $overlaps, 'Two cells hold the same point of the tile.');
    }

    #[DataProvider('seeds')]
    public function testEveryCellCrossingAnEdgeHasItsTranslate(int $seed): void
    {
        $voronoi = Voronoi::create(size: 120, thickness: 1.4, sites: 14, seed: $seed);
        $cells = Shapes::polygons($voronoi);
        $size = $voronoi->tileWidth();

        self::assertGreaterThan(0, self::countEdgeCrossings($cells, $size, $size, 0.7, 0.7));
        self::assertPointListsJoin($cells, $size, $size, 'cell', 0.7, 0.7);
    }

    public function testEveryCellIsAClosedPolygonOfThreeCornersOrMore(): void
    {
        foreach (Shapes::polygons(Voronoi::create(size: 120, thickness: 1.4, sites: 14)) as $cell) {
            self::assertGreaterThanOrEqual(3, \count($cell));
            self::assertGreaterThan(0.0, Region::area($cell));
        }
    }

    public function testTwoSeedsAreTwoDiagrams(): void
    {
        $one = Shapes::polygons(Voronoi::create(size: 120, thickness: 1.4, sites: 14, seed: 1));
        $other = Shapes::polygons(Voronoi::create(size: 120, thickness: 1.4, sites: 14, seed: 2));

        self::assertNotSame($one, $other);
    }

    public function testTheSameSeedDrawsTheSameDiagram(): void
    {
        self::assertSame(
            Shapes::polygons(Voronoi::create(size: 120, thickness: 1.4, sites: 14, seed: 5)),
            Shapes::polygons(Voronoi::create(size: 120, thickness: 1.4, sites: 14, seed: 5)),
        );
    }

    public function testTheCellsAreOutlined(): void
    {
        foreach (Shapes::of(Voronoi::create(size: 120, thickness: 2.5, sites: 6)) as $cell) {
            self::assertSame('none', $cell->getAttribute('fill'));
            self::assertSame('2.5', $cell->getAttribute('stroke-width'));
        }
    }

    public function testTheCellsArePaintedWithThePatternColorWhenFilled(): void
    {
        $painted = 0;

        foreach (Shapes::of(Voronoi::create(size: 120, thickness: 1.4, sites: 8, filled: true, minOpacity: 0.2, maxOpacity: 0.6)) as $shape) {
            $tone = $shape->getAttribute('fill-opacity');

            if (null === $tone) {
                continue;
            }

            ++$painted;

            self::assertSame('currentColor', $shape->getAttribute('fill'));
            self::assertGreaterThanOrEqual(0.2, (float) $tone);
            self::assertLessThanOrEqual(0.6, (float) $tone);
        }

        self::assertGreaterThan(0, $painted);
    }

    public function testEveryCellIsPaintedBeforeTheFirstOutlineIsDrawn(): void
    {
        $roles = array_map(
            static fn (ElementInterface $shape): string => null === $shape->getAttribute('fill-opacity') ? 'stroke' : 'fill',
            Shapes::of(Voronoi::create(size: 120, thickness: 2, sites: 8, filled: true)),
        );

        $cells = intdiv(\count($roles), 2);

        self::assertSame([...array_fill(0, $cells, 'fill'), ...array_fill(0, $cells, 'stroke')], $roles);
    }

    /**
     * The tone belongs to the seed, not to the order the polygons come out in.
     * A cell reaching over an edge is drawn again a period away, and if the two
     * copies took their tone from their rank the seam would show in colour.
     */
    #[DataProvider('seeds')]
    public function testACellRepeatedAcrossAnEdgeKeepsTheToneOfItsSeed(int $seed): void
    {
        $voronoi = Voronoi::create(size: 120, thickness: 1.4, sites: 14, seed: $seed, filled: true);
        $cells = self::paintedCells($voronoi);
        $size = $voronoi->tileWidth();

        $repeated = 0;

        foreach ($cells as [$points, $tone]) {
            foreach ($cells as [$otherPoints, $otherTone]) {
                if (!self::isLatticeTranslateOf($points, $otherPoints, $size)) {
                    continue;
                }

                ++$repeated;

                self::assertSame($tone, $otherTone, 'A cell repeated a period away changes tone across the seam.');
            }
        }

        self::assertGreaterThan(0, $repeated, 'No cell reached over an edge, so nothing was checked.');
    }

    public function testAFilledTileCanDoWithoutAnOutline(): void
    {
        $shapes = Shapes::of(Voronoi::create(size: 120, thickness: 0, sites: 8, filled: true));

        self::assertNotEmpty($shapes);

        foreach ($shapes as $shape) {
            self::assertNull($shape->getAttribute('stroke'));
            self::assertNotNull($shape->getAttribute('fill-opacity'));
        }
    }

    public function testFillingLeavesTheCellsWhereTheyWere(): void
    {
        $filled = Shapes::polygons(Voronoi::create(size: 120, thickness: 1.4, sites: 14, seed: 3, filled: true));

        self::assertSame(
            Shapes::polygons(Voronoi::create(size: 120, thickness: 1.4, sites: 14, seed: 3)),
            \array_slice($filled, intdiv(\count($filled), 2)),
        );
    }

    public function testTheBoundsTellTwoFilledTilesApartAndAreIgnoredOtherwise(): void
    {
        self::assertNotSame(Voronoi::create()->id(), Voronoi::create(filled: true)->id());
        self::assertNotSame(Voronoi::create(filled: true)->id(), Voronoi::create(filled: true, maxOpacity: 0.9)->id());
        self::assertSame(Voronoi::create()->id(), Voronoi::create(minOpacity: 0.4)->id());
    }

    public function testSizeMustBePositive(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('size must be a finite number greater than 0, got 0.');

        Voronoi::create(size: 0);
    }

    public function testANegativeThicknessIsRejected(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('thickness must be between 0 and 30, got -1.');

        Voronoi::create(size: 120, thickness: -1);
    }

    public function testALineThickerThanAQuarterOfTheSizeIsRejected(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('thickness must be between 0 and 30, got 31.');

        Voronoi::create(size: 120, thickness: 31);
    }

    public function testATileWithNeitherFillNorOutlineIsRejected(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('thickness must be greater than 0 unless the cells are filled, got 0.');

        Voronoi::create(size: 120, thickness: 0);
    }

    public function testAPaleBoundOutsideTheRangeIsRejected(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('minOpacity must be between 0 and 1, got -0.1.');

        Voronoi::create(size: 120, filled: true, minOpacity: -0.1);
    }

    public function testADeepBoundOutsideTheRangeIsRejected(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('maxOpacity must be between 0 and 1, got 1.2.');

        Voronoi::create(size: 120, filled: true, maxOpacity: 1.2);
    }

    public function testBoundsGivenTheWrongWayRoundAreRejected(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('minOpacity must not exceed maxOpacity (0.3), got 0.6.');

        Voronoi::create(size: 120, filled: true, minOpacity: 0.6, maxOpacity: 0.3);
    }

    public function testASingleSeedIsRejected(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('sites must be at least 2, got 1.');

        Voronoi::create(size: 120, thickness: 1.4, sites: 1);
    }

    /**
     * The cells the tile paints, as their corners and the tone they carry.
     *
     * @return list<array{list<array{float, float}>, string}>
     */
    private static function paintedCells(Voronoi $voronoi): array
    {
        $cells = [];

        foreach (Shapes::of($voronoi) as $shape) {
            $tone = $shape->getAttribute('fill-opacity');

            if (null === $tone) {
                continue;
            }

            $corners = [];

            foreach (explode(' ', (string) $shape->getAttribute('points')) as $pair) {
                [$x, $y] = explode(',', $pair);
                $corners[] = [(float) $x, (float) $y];
            }

            $cells[] = [$corners, $tone];
        }

        return $cells;
    }

    /**
     * Whether the second corner list is the first moved by a whole number of
     * periods, which is what a cell reaching over an edge is drawn as.
     *
     * @param list<array{float, float}> $corners
     * @param list<array{float, float}> $others
     */
    private static function isLatticeTranslateOf(array $corners, array $others, float $size): bool
    {
        if (\count($corners) !== \count($others) || [] === $corners) {
            return false;
        }

        $dx = $others[0][0] - $corners[0][0];
        $dy = $others[0][1] - $corners[0][1];

        if (abs($dx) < 0.001 && abs($dy) < 0.001) {
            return false;
        }

        foreach ([$dx, $dy] as $delta) {
            if (abs($delta - round($delta / $size) * $size) > 0.001) {
                return false;
            }
        }

        foreach ($corners as $index => [$x, $y]) {
            if (abs($others[$index][0] - $x - $dx) > 0.001 || abs($others[$index][1] - $y - $dy) > 0.001) {
                return false;
            }
        }

        return true;
    }
}
