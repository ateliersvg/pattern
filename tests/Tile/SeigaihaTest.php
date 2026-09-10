<?php

declare(strict_types=1);

namespace Atelier\Pattern\Tests\Tile;

use Atelier\Pattern\Exception\InvalidArgumentException;
use Atelier\Pattern\Tests\Support\Shapes;
use Atelier\Pattern\Tile\Seigaiha;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(Seigaiha::class)]
final class SeigaihaTest extends TestCase
{
    public function testSubnormalIntersectionsDoNotEmitNonFiniteCoordinates(): void
    {
        $paths = Shapes::subpaths(Seigaiha::create(radius: 2e-161, rise: 2.5e-162, rings: 4, thickness: 2e-163));

        self::assertNotEmpty($paths);
        foreach ($paths as $path) {
            self::assertStringNotContainsString('NAN', $path);
            self::assertStringNotContainsString('INF', $path);
        }
    }

    public function testTheTileIsTwoFansAcrossAndTwoRowsDown(): void
    {
        $seigaiha = Seigaiha::create(radius: 26, rise: 12);

        self::assertSame(52.0, $seigaiha->tileWidth());
        self::assertSame(24.0, $seigaiha->tileHeight());
    }

    public function testEveryArcBelongsToARingOfAFan(): void
    {
        $seigaiha = Seigaiha::create(radius: 26, rise: 12, rings: 4);
        $radii = [26.0, 19.5, 13.0, 6.5];

        foreach (Shapes::subpaths($seigaiha) as $subpath) {
            $numbers = Shapes::numbersIn($subpath);

            // M x y A r r 0 flag 1 x y
            self::assertContains(round($numbers[2], 4), $radii);
            self::assertSame($numbers[2], $numbers[3]);
        }
    }

    /**
     * Every arc runs on the upper half of its own fan, which is the half a fan
     * in front cannot have taken.
     */
    public function testEveryArcRunsOnTheCircleOfSomeFan(): void
    {
        $seigaiha = Seigaiha::create(radius: 26, rise: 12);

        foreach (Shapes::subpaths($seigaiha) as $subpath) {
            [$x1, $y1, $radius, , , , , $x2, $y2] = Shapes::numbersIn($subpath);

            $matched = false;

            foreach ([[0.0, 0.0], [26.0, 12.0]] as [$cx, $cy]) {
                foreach ([-52.0, 0.0, 52.0] as $dx) {
                    foreach ([-24.0, 0.0, 24.0] as $dy) {
                        $first = hypot($x1 - $cx - $dx, $y1 - $cy - $dy);
                        $second = hypot($x2 - $cx - $dx, $y2 - $cy - $dy);

                        if (abs($first - $radius) < 0.01 && abs($second - $radius) < 0.01) {
                            $matched = true;
                        }
                    }
                }
            }

            self::assertTrue($matched, 'Arc '.$subpath.' sits on no fan of the tile.');
        }
    }

    /**
     * The rise is what the pattern is: rows close together take most of the fan
     * behind them, rows further apart leave more of it standing.
     */
    public function testACloserRowTakesMoreOfTheFanBehindIt(): void
    {
        self::assertGreaterThan(
            $this->survivingSpan(Seigaiha::create(radius: 26, rise: 4)),
            $this->survivingSpan(Seigaiha::create(radius: 26, rise: 13)),
        );
    }

    public function testTheArcsAreDrawnNotFilled(): void
    {
        foreach (Shapes::of(Seigaiha::create(radius: 26, thickness: 2)) as $shape) {
            self::assertSame('none', $shape->getAttribute('fill'));
            self::assertSame('2', $shape->getAttribute('stroke-width'));
        }
    }

    public function testARiseOfMoreThanHalfTheRadiusIsRejected(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('rise must not exceed half the radius (13), got 20.');

        Seigaiha::create(radius: 26, rise: 20);
    }

    public function testAFanOfOneRingIsRejected(): void
    {
        $this->expectException(InvalidArgumentException::class);

        Seigaiha::create(radius: 26, rings: 1);
    }

    /**
     * How much of the outer ring of the fan at the origin is still drawn, in
     * radians. Every span is at most half a turn, so the chord gives the angle
     * back without an ambiguity.
     */
    private function survivingSpan(Seigaiha $seigaiha): float
    {
        $total = 0.0;

        foreach (Shapes::subpaths($seigaiha) as $subpath) {
            [$x1, $y1, $radius, , , , , $x2, $y2] = Shapes::numbersIn($subpath);

            if (abs($radius - 26.0) > 0.01) {
                continue;
            }

            if (abs(hypot($x1, $y1) - $radius) > 0.01 || abs(hypot($x2, $y2) - $radius) > 0.01) {
                continue;
            }

            $total += 2 * asin(min(1.0, hypot($x2 - $x1, $y2 - $y1) / (2 * $radius)));
        }

        return $total;
    }
}
