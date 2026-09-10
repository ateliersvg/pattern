<?php

declare(strict_types=1);

namespace Atelier\Pattern\Tests\Tile;

use Atelier\Pattern\Exception\InvalidArgumentException;
use Atelier\Pattern\Tests\Support\Shapes;
use Atelier\Pattern\Tile\Honeycomb;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(Honeycomb::class)]
final class HoneycombTest extends TestCase
{
    private const float TOLERANCE = 0.0001;

    public function testTileHoldsTwoRowsOfHexagons(): void
    {
        $comb = Honeycomb::create(radius: 12, thickness: 1);

        self::assertEqualsWithDelta(sqrt(3) * 12, $comb->tileWidth(), self::TOLERANCE);
        self::assertSame(36.0, $comb->tileHeight());
    }

    public function testEveryPolygonIsAHexagonStandingOnAPoint(): void
    {
        $radius = 12.0;

        foreach (Shapes::polygons(Honeycomb::create(radius: $radius, thickness: 1)) as $hexagon) {
            self::assertCount(6, $hexagon);

            [$cx, $cy] = $this->centreOf($hexagon);

            foreach ($hexagon as [$x, $y]) {
                self::assertEqualsWithDelta($radius, hypot($x - $cx, $y - $cy), self::TOLERANCE);
            }

            self::assertEqualsWithDelta($cx, $hexagon[0][0], self::TOLERANCE);
            self::assertEqualsWithDelta($cy - $radius, $hexagon[0][1], self::TOLERANCE);
        }
    }

    public function testHexagonsSitOnEveryLatticePointOfTheTile(): void
    {
        $comb = Honeycomb::create(radius: 12, thickness: 1);
        $width = $comb->tileWidth();
        $height = $comb->tileHeight();

        $centres = array_map($this->centreOf(...), Shapes::polygons($comb));

        self::assertCount(5, $centres);

        foreach ([[0.0, 0.0], [$width, 0.0], [$width / 2, $height / 2], [0.0, $height], [$width, $height]] as $expected) {
            self::assertTrue($this->hasCentre($centres, $expected), \sprintf('No hexagon centred on (%s, %s).', $expected[0], $expected[1]));
        }
    }

    public function testEveryHexagonCrossingAnEdgeHasItsTranslate(): void
    {
        $comb = Honeycomb::create(radius: 12, thickness: 1);
        $width = $comb->tileWidth();
        $height = $comb->tileHeight();

        $polygons = Shapes::polygons($comb);
        $centres = array_map($this->centreOf(...), $polygons);

        $crossing = 0;

        foreach ($polygons as $hexagon) {
            [$cx, $cy] = $this->centreOf($hexagon);
            $xs = array_column($hexagon, 0);
            $ys = array_column($hexagon, 1);

            $shifts = [];

            if (min($xs) < -self::TOLERANCE) {
                $shifts[] = [$width, 0.0];
            }

            if (max($xs) > $width + self::TOLERANCE) {
                $shifts[] = [-$width, 0.0];
            }

            if (min($ys) < -self::TOLERANCE) {
                $shifts[] = [0.0, $height];
            }

            if (max($ys) > $height + self::TOLERANCE) {
                $shifts[] = [0.0, -$height];
            }

            if (\count($shifts) > 1) {
                $shifts[] = [$shifts[0][0] + $shifts[1][0], $shifts[0][1] + $shifts[1][1]];
            }

            foreach ($shifts as [$dx, $dy]) {
                ++$crossing;
                self::assertTrue(
                    $this->hasCentre($centres, [$cx + $dx, $cy + $dy]),
                    \sprintf('The hexagon at (%s, %s) crosses an edge but its translate by (%s, %s) is missing.', $cx, $cy, $dx, $dy),
                );
            }
        }

        self::assertGreaterThan(0, $crossing);
    }

    public function testTheHexagonsAreOutlined(): void
    {
        foreach (Shapes::of(Honeycomb::create(radius: 12, thickness: 1.4)) as $shape) {
            self::assertSame('none', $shape->getAttribute('fill'));
            self::assertSame('1.4', $shape->getAttribute('stroke-width'));
        }
    }

    public function testALineThickerThanTheRadiusIsRejected(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('thickness must not exceed radius (12), got 13.');

        Honeycomb::create(radius: 12, thickness: 13);
    }

    public function testRadiusMustBePositive(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('radius must be a finite number greater than 0');

        Honeycomb::create(radius: 0);
    }

    public function testThicknessMustBePositive(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('thickness must be a finite number greater than 0');

        Honeycomb::create(radius: 12, thickness: 0);
    }

    /**
     * @param list<array{0: float, 1: float}> $hexagon
     *
     * @return array{0: float, 1: float}
     */
    private function centreOf(array $hexagon): array
    {
        return [
            array_sum(array_column($hexagon, 0)) / \count($hexagon),
            array_sum(array_column($hexagon, 1)) / \count($hexagon),
        ];
    }

    /**
     * @param list<array{0: float, 1: float}> $centres
     * @param array{0: float, 1: float}       $wanted
     */
    private function hasCentre(array $centres, array $wanted): bool
    {
        foreach ($centres as $centre) {
            if (abs($centre[0] - $wanted[0]) < self::TOLERANCE && abs($centre[1] - $wanted[1]) < self::TOLERANCE) {
                return true;
            }
        }

        return false;
    }
}
