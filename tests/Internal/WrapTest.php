<?php

declare(strict_types=1);

namespace Atelier\Pattern\Tests\Internal;

use Atelier\Pattern\Internal\Wrap;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(Wrap::class)]
final class WrapTest extends TestCase
{
    public function testAShapeInsideTheTileIsDrawnOnce(): void
    {
        self::assertSame([[0.0, 0.0]], Wrap::offsets(2.0, 2.0, 8.0, 8.0, 10.0, 10.0));
    }

    public function testAShapeTouchingAnEdgeIsDrawnOnce(): void
    {
        self::assertSame([[0.0, 0.0]], Wrap::offsets(0.0, 0.0, 10.0, 10.0, 10.0, 10.0));
    }

    public function testAShapeOverTheLeftEdgeComesBackOnTheRight(): void
    {
        self::assertSame([[0.0, 0.0], [10.0, 0.0]], Wrap::offsets(-1.0, 2.0, 3.0, 8.0, 10.0, 10.0));
    }

    public function testAShapeOverTheRightEdgeComesBackOnTheLeft(): void
    {
        self::assertSame([[0.0, 0.0], [-10.0, 0.0]], Wrap::offsets(7.0, 2.0, 11.0, 8.0, 10.0, 10.0));
    }

    public function testAShapeOverTheTopEdgeComesBackAtTheBottom(): void
    {
        self::assertSame([[0.0, 0.0], [0.0, 10.0]], Wrap::offsets(2.0, -1.0, 8.0, 3.0, 10.0, 10.0));
    }

    public function testAShapeOverTheBottomEdgeComesBackAtTheTop(): void
    {
        self::assertSame([[0.0, 0.0], [0.0, -10.0]], Wrap::offsets(2.0, 7.0, 8.0, 11.0, 10.0, 10.0));
    }

    public function testAShapeOnACornerIsDrawnOnAllFour(): void
    {
        self::assertSame(
            [[0.0, 0.0], [0.0, 10.0], [10.0, 0.0], [10.0, 10.0]],
            Wrap::offsets(-1.0, -1.0, 3.0, 3.0, 10.0, 10.0),
        );
    }

    public function testTheTileIsNotAssumedSquare(): void
    {
        self::assertSame(
            [[0.0, 0.0], [0.0, -30.0], [-20.0, 0.0], [-20.0, -30.0]],
            Wrap::offsets(18.0, 28.0, 22.0, 32.0, 20.0, 30.0),
        );
    }
}
