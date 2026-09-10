<?php

declare(strict_types=1);

namespace Atelier\Pattern\Tests\Tile;

use Atelier\Pattern\Exception\InvalidArgumentException;
use Atelier\Pattern\Tests\Support\Shapes;
use Atelier\Pattern\Tests\Support\TilingAssertions;
use Atelier\Pattern\Tile\Checker;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(Checker::class)]
final class CheckerTest extends TestCase
{
    use TilingAssertions;

    public function testTileSpansTwoCellsEachWay(): void
    {
        $checker = Checker::create(size: 12);

        self::assertSame(24.0, $checker->tileWidth());
        self::assertSame(24.0, $checker->tileHeight());
    }

    public function testTwoCellsAreFilledOnADiagonal(): void
    {
        $checker = Checker::create(size: 12);

        self::assertSame([
            ['x' => 0.0, 'y' => 0.0, 'width' => 12.0, 'height' => 12.0],
            ['x' => 12.0, 'y' => 12.0, 'width' => 12.0, 'height' => 12.0],
        ], Shapes::rects($checker));

        self::assertCirclesAndRectsJoin($checker);
    }

    public function testTheOtherTwoCellsAreLeftTransparent(): void
    {
        self::assertCount(2, Shapes::rects(Checker::create(size: 12)));
    }

    public function testSizeMustBePositive(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('size must be a finite number greater than 0');

        Checker::create(size: 0);
    }
}
