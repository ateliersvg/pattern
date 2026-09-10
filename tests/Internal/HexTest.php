<?php

declare(strict_types=1);

namespace Atelier\Pattern\Tests\Internal;

use Atelier\Pattern\Internal\Hex;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(Hex::class)]
final class HexTest extends TestCase
{
    private const float TOLERANCE = 0.0001;

    public function testTheTileIsOneHexagonAcrossAndTwoRowsDown(): void
    {
        self::assertEqualsWithDelta(sqrt(3) * 12, Hex::tileWidth(12), self::TOLERANCE);
        self::assertSame(36.0, Hex::tileHeight(12));
    }

    public function testTheTileHoldsFourCornersAndAMiddle(): void
    {
        $width = Hex::tileWidth(12);
        $height = Hex::tileHeight(12);

        self::assertSame(
            [[0.0, 0.0], [$width, 0.0], [$width / 2, $height / 2], [0.0, $height], [$width, $height]],
            Hex::centres(12),
        );
    }

    public function testTheHexagonStandsOnAPoint(): void
    {
        $radius = 12.0;
        $vertices = Hex::vertices(5.0, 7.0, $radius);

        self::assertCount(6, $vertices);
        self::assertSame([5.0, 7.0 - $radius], $vertices[0]);

        foreach ($vertices as $corner => [$x, $y]) {
            self::assertEqualsWithDelta($radius, hypot($x - 5.0, $y - 7.0), self::TOLERANCE, sprintf('Corner %d is off the circle.', $corner));
        }
    }

    public function testTheVerticesTurnClockwise(): void
    {
        $vertices = Hex::vertices(0.0, 0.0, 10.0);

        // Clockwise on a y down axis means a negative signed area.
        $twice = 0.0;

        foreach ($vertices as $corner => [$x, $y]) {
            [$nextX, $nextY] = $vertices[($corner + 1) % 6];
            $twice += $x * $nextY - $nextX * $y;
        }

        self::assertGreaterThan(0.0, $twice);
    }

    public function testTheFlatSidesStandOnTheVerticalEdgesOfTheTile(): void
    {
        $radius = 12.0;
        $vertices = Hex::vertices(0.0, 0.0, $radius);

        self::assertEqualsWithDelta(Hex::tileWidth($radius) / 2, $vertices[1][0], self::TOLERANCE);
        self::assertEqualsWithDelta($vertices[1][0], $vertices[2][0], self::TOLERANCE);
        self::assertEqualsWithDelta(-$vertices[1][0], $vertices[5][0], self::TOLERANCE);
    }
}
