<?php

declare(strict_types=1);

namespace Atelier\Pattern\Tests\Tile;

use Atelier\Pattern\Exception\InvalidArgumentException;
use Atelier\Pattern\Tests\Support\Shapes;
use Atelier\Pattern\Tile\GreekKey;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(GreekKey::class)]
final class GreekKeyTest extends TestCase
{
    public function testTheTileIsSquare(): void
    {
        $key = GreekKey::create(size: 40);

        self::assertSame(40.0, $key->tileWidth());
        self::assertSame(40.0, $key->tileHeight());
    }

    public function testTheRuleRunsTheFullWidthSoTheRowReadsAsOneBand(): void
    {
        [$rule] = Shapes::subpaths(GreekKey::create(size: 40));
        $points = Shapes::pointsIn($rule);

        self::assertSame([[0.0, 35.0], [40.0, 35.0]], $points);
    }

    public function testTheKeyTurnsSixTimesAndSpiralsInwards(): void
    {
        $points = Shapes::pointsIn(Shapes::subpaths(GreekKey::create(size: 40))[1]);

        $lengths = [];

        for ($node = 1; $node < \count($points); ++$node) {
            $lengths[] = hypot(
                $points[$node][0] - $points[$node - 1][0],
                $points[$node][1] - $points[$node - 1][1],
            );
        }

        self::assertSame([30.0, 30.0, 20.0, 20.0, 10.0, 10.0], $lengths);
    }

    public function testNothingCrossesAnEdge(): void
    {
        foreach (Shapes::subpaths(GreekKey::create(size: 40)) as $subpath) {
            foreach (Shapes::pointsIn($subpath) as [$x, $y]) {
                self::assertGreaterThanOrEqual(0.0, $x);
                self::assertLessThanOrEqual(40.0, $x);
                self::assertGreaterThanOrEqual(0.0, $y);
                self::assertLessThanOrEqual(40.0, $y);
            }
        }
    }

    public function testTheKeyIsDrawnNotFilled(): void
    {
        foreach (Shapes::of(GreekKey::create(size: 40, thickness: 2)) as $shape) {
            self::assertSame('none', $shape->getAttribute('fill'));
            self::assertSame('2', $shape->getAttribute('stroke-width'));
        }
    }

    public function testALineThickerThanAUnitIsRejected(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('thickness must not exceed an eighth of the size (5), got 6.');

        GreekKey::create(size: 40, thickness: 6);
    }

    public function testSizeMustBePositive(): void
    {
        $this->expectException(InvalidArgumentException::class);

        GreekKey::create(size: 0);
    }
}
