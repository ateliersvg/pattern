<?php

declare(strict_types=1);

namespace Atelier\Pattern\Tests\Tile;

use Atelier\Pattern\Exception\InvalidArgumentException;
use Atelier\Pattern\Tests\Support\Shapes;
use Atelier\Pattern\Tests\Support\TilingAssertions;
use Atelier\Pattern\Tile\Asanoha;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(Asanoha::class)]
final class AsanohaTest extends TestCase
{
    use TilingAssertions;

    public function testTheTileIsTheOneOfTheHexagonalLattice(): void
    {
        $asanoha = Asanoha::create(size: 20);

        self::assertEqualsWithDelta(sqrt(3) * 20, $asanoha->tileWidth(), 0.0001);
        self::assertSame(60.0, $asanoha->tileHeight());
    }

    /**
     * Per hexagon: one outline, three long diagonals, and per triangle a pair
     * of spokes drawn in one stroke plus the one leaving the middle.
     */
    public function testEveryHexagonCarriesItsOutlineItsDiagonalsAndItsSixLeaves(): void
    {
        $lines = self::linesOf(Asanoha::create(size: 28, thickness: 1.2));

        $lengths = array_count_values(array_map(\count(...), $lines));

        self::assertSame(5, $lengths[7], 'One closed outline per hexagon.');
        self::assertSame(30, $lengths[3], 'Six pairs of spokes per hexagon.');
        self::assertSame(45, $lengths[2], 'Three diagonals and six middle spokes per hexagon.');
    }

    public function testEveryPairOfSpokesMeetsAtTheMiddleOfItsTriangle(): void
    {
        $size = 28.0;
        $reach = $size / sqrt(3);

        $pairs = 0;

        foreach (self::linesOf(Asanoha::create(size: $size, thickness: 1.2)) as $line) {
            if (3 !== \count($line)) {
                continue;
            }

            [$near, $middle, $far] = $line;

            ++$pairs;

            // The triangle is equilateral on a side of the hexagon, so its
            // middle stands one third of the way in from each corner.
            self::assertEqualsWithDelta($size, hypot($far[0] - $near[0], $far[1] - $near[1]), 0.0001);
            self::assertEqualsWithDelta($reach, hypot($middle[0] - $near[0], $middle[1] - $near[1]), 0.0001);
            self::assertEqualsWithDelta($reach, hypot($far[0] - $middle[0], $far[1] - $middle[1]), 0.0001);
        }

        self::assertSame(30, $pairs);
    }

    public function testEveryLineCrossingAnEdgeHasItsTranslate(): void
    {
        $asanoha = Asanoha::create(size: 28, thickness: 1.2);
        $lines = self::linesOf($asanoha);
        $width = $asanoha->tileWidth();
        $height = $asanoha->tileHeight();

        // The vertical sides of the middle hexagon lie on the vertical edges on
        // purpose: the hexagon next door draws the other half of that line, so
        // there is nothing to carry across along that axis.
        self::assertGreaterThan(0, self::countEdgeCrossings($lines, $width, $height, 0.0, 0.6));
        self::assertPointListsJoin($lines, $width, $height, 'line', 0.0, 0.6);
    }

    public function testTheLinesAreDrawnNotFilled(): void
    {
        foreach (Shapes::of(Asanoha::create(size: 28, thickness: 1.6)) as $shape) {
            self::assertSame('none', $shape->getAttribute('fill'));
            self::assertSame('1.6', $shape->getAttribute('stroke-width'));
        }
    }

    public function testSizeMustBePositive(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('size must be a finite number greater than 0, got 0.');

        Asanoha::create(size: 0);
    }

    public function testThicknessMustBePositive(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('thickness must be a finite number greater than 0, got 0.');

        Asanoha::create(size: 28, thickness: 0);
    }

    public function testALineThickerThanAQuarterOfTheSizeIsRejected(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('thickness must not exceed a quarter of the size (7), got 8.');

        Asanoha::create(size: 28, thickness: 8);
    }

    /**
     * @return list<list<array{0: float, 1: float}>>
     */
    private static function linesOf(Asanoha $asanoha): array
    {
        return array_map(Shapes::pointsIn(...), Shapes::subpaths($asanoha));
    }
}
