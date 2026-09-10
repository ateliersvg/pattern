<?php

declare(strict_types=1);

namespace Atelier\Pattern\Tests\Tile;

use Atelier\Pattern\Exception\InvalidArgumentException;
use Atelier\Pattern\Tests\Support\Shapes;
use Atelier\Pattern\Tests\Support\TilingAssertions;
use Atelier\Pattern\Tile\Grid;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(Grid::class)]
final class GridTest extends TestCase
{
    use TilingAssertions;

    public function testTileIsOneCellSquare(): void
    {
        $grid = Grid::create(size: 18, thickness: 1);

        self::assertSame(18.0, $grid->tileWidth());
        self::assertSame(18.0, $grid->tileHeight());
    }

    public function testRulesAreFilledBandsHeldInsideTheTile(): void
    {
        $grid = Grid::create(size: 18, thickness: 2);

        self::assertSame([
            ['x' => 0.0, 'y' => 0.0, 'width' => 2.0, 'height' => 18.0],
            ['x' => 0.0, 'y' => 0.0, 'width' => 18.0, 'height' => 2.0],
        ], Shapes::rects($grid));

        self::assertCirclesAndRectsJoin($grid);
    }

    public function testRulesKeepTheirFullThickness(): void
    {
        foreach (Shapes::rects(Grid::create(size: 18, thickness: 2)) as $rect) {
            self::assertSame(2.0, min($rect['width'], $rect['height']));
        }
    }

    public function testARuleThickerThanTheCellIsRejected(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('thickness must not exceed size (18), got 19.');

        Grid::create(size: 18, thickness: 19);
    }

    public function testSizeMustBePositive(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('size must be a finite number greater than 0');

        Grid::create(size: -1);
    }

    public function testThicknessMustBePositive(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('thickness must be a finite number greater than 0');

        Grid::create(size: 18, thickness: 0);
    }
}
