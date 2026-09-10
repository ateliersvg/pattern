<?php

declare(strict_types=1);

namespace Atelier\Pattern\Tests\Tile;

use Atelier\Pattern\Exception\InvalidArgumentException;
use Atelier\Pattern\Tests\Support\Region;
use Atelier\Pattern\Tests\Support\Shapes;
use Atelier\Pattern\Tests\Support\TilingAssertions;
use Atelier\Pattern\Tile\Houndstooth;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(Houndstooth::class)]
final class HoundstoothTest extends TestCase
{
    use TilingAssertions;

    /** Samples across the tile, per axis. Prime, and read off centre, so none lands on an edge of the motif. */
    private const int SAMPLES = 41;

    private const float OFFSET = 0.3;

    public function testTheTileIsTheSquareItWasGiven(): void
    {
        $houndstooth = Houndstooth::create(size: 32);

        self::assertSame(32.0, $houndstooth->tileWidth());
        self::assertSame(32.0, $houndstooth->tileHeight());
    }

    public function testTheMotifIsASquareAndTwoBandsCutInTwo(): void
    {
        $pieces = self::piecesOf(Houndstooth::create(size: 24));

        self::assertCount(5, $pieces);
        self::assertSame([4, 4, 3, 4, 3], array_map(\count(...), $pieces));
    }

    public function testTheMotifTakesHalfTheTile(): void
    {
        $filled = 0.0;

        foreach (self::piecesOf(Houndstooth::create(size: 24)) as $piece) {
            $filled += Region::area($piece);
        }

        self::assertEqualsWithDelta(24 * 24 / 2, $filled, 0.0001);
    }

    /**
     * The rule the check is broken by: what the tile leaves empty is the motif
     * itself, moved half a tile across and half a tile down.
     */
    public function testWhatTheTileLeavesEmptyIsTheMotifMovedHalfATile(): void
    {
        $houndstooth = Houndstooth::create(size: 24);
        $pieces = self::piecesOf($houndstooth);
        $size = $houndstooth->tileWidth();

        $wrong = 0;

        for ($column = 0; $column < self::SAMPLES; ++$column) {
            for ($row = 0; $row < self::SAMPLES; ++$row) {
                $x = ($column + self::OFFSET) * $size / self::SAMPLES;
                $y = ($row + self::OFFSET) * $size / self::SAMPLES;

                $here = Region::cover($pieces, $x, $y);
                $there = Region::cover($pieces, fmod($x + $size / 2, $size), fmod($y + $size / 2, $size));

                $wrong += 1 === $here + $there ? 0 : 1;
            }
        }

        self::assertSame(0, $wrong, 'The empty half is not the motif moved half a tile.');
    }

    public function testAFilledMotifStaysInsideTheTile(): void
    {
        $houndstooth = Houndstooth::create(size: 24);

        self::assertSame(0, self::countEdgeCrossings(self::piecesOf($houndstooth), $houndstooth->tileWidth(), $houndstooth->tileHeight()));
    }

    public function testALineOnTheFillIsDrawnAgainAcrossEveryEdgeItCrosses(): void
    {
        $houndstooth = Houndstooth::create(size: 24, thickness: 3);
        $pieces = self::piecesOf($houndstooth);
        $size = $houndstooth->tileWidth();

        self::assertGreaterThan(0, self::countEdgeCrossings($pieces, $size, $size, 1.5, 1.5));
        self::assertPointListsJoin($pieces, $size, $size, 'piece', 1.5, 1.5);
    }

    public function testTheMotifIsFilledAndLeftUnstrokedUntilAskedFor(): void
    {
        foreach (Shapes::of(Houndstooth::create(size: 24)) as $motif) {
            self::assertSame('currentColor', $motif->getAttribute('fill'));
            self::assertNull($motif->getAttribute('stroke'));
            self::assertNull($motif->getAttribute('stroke-width'));
        }
    }

    public function testALineWidthIsLaidOnTopOfTheFill(): void
    {
        foreach (Shapes::of(Houndstooth::create(size: 24, thickness: 1.5)) as $motif) {
            self::assertSame('currentColor', $motif->getAttribute('fill'));
            self::assertSame('currentColor', $motif->getAttribute('stroke'));
            self::assertSame('1.5', $motif->getAttribute('stroke-width'));
        }
    }

    public function testSizeMustBePositive(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('size must be a finite number greater than 0, got 0.');

        Houndstooth::create(size: 0);
    }

    public function testANegativeLineWidthIsRejected(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('thickness must be between 0 and 6, got -1.');

        Houndstooth::create(size: 24, thickness: -1);
    }

    public function testALineThickerThanASubUnitIsRejected(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('thickness must be between 0 and 6, got 7.');

        Houndstooth::create(size: 24, thickness: 7);
    }

    /**
     * @return list<list<array{0: float, 1: float}>>
     */
    private static function piecesOf(Houndstooth $houndstooth): array
    {
        return array_map(Shapes::pointsIn(...), Shapes::subpaths($houndstooth));
    }
}
