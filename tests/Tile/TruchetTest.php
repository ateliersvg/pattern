<?php

declare(strict_types=1);

namespace Atelier\Pattern\Tests\Tile;

use Atelier\Pattern\Exception\InvalidArgumentException;
use Atelier\Pattern\Tests\Support\Shapes;
use Atelier\Pattern\Tile\Truchet;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(Truchet::class)]
final class TruchetTest extends TestCase
{
    private const float TOLERANCE = 0.0001;

    public function testTheTileHoldsAsManyCellsAsAsked(): void
    {
        $truchet = Truchet::create(tile: 20, thickness: 2, cells: 6, seed: 1);

        self::assertSame(120.0, $truchet->tileWidth());
        self::assertSame(120.0, $truchet->tileHeight());
    }

    public function testEveryCellCarriesTwoQuarters(): void
    {
        $subpaths = Shapes::subpaths(Truchet::create(tile: 20, thickness: 2, cells: 4, seed: 1));

        self::assertCount(2 * 4 * 4, $subpaths);
    }

    public function testAQuarterHasTheRadiusOfHalfACell(): void
    {
        foreach (Shapes::subpaths(Truchet::create(tile: 20, thickness: 2, cells: 4, seed: 1)) as $subpath) {
            $numbers = Shapes::numbersIn($subpath);

            self::assertSame(10.0, $numbers[2], 'Half a cell is the horizontal radius.');
            self::assertSame(10.0, $numbers[3], 'Half a cell is the vertical radius.');
            self::assertEqualsWithDelta(10.0 * sqrt(2), hypot($numbers[7] - $numbers[0], $numbers[8] - $numbers[1]), self::TOLERANCE, 'A quarter turn subtends a chord of r times the square root of two.');
        }
    }

    public function testEveryQuarterEndsOnTheMiddleOfACellEdge(): void
    {
        $tile = 20.0;

        foreach ($this->endsOf(Truchet::create(tile: $tile, thickness: 2, cells: 4, seed: 1)) as [$x, $y]) {
            $onVerticalEdge = $this->isMultipleOf($x, $tile) && $this->isMiddleOf($y, $tile);
            $onHorizontalEdge = $this->isMiddleOf($x, $tile) && $this->isMultipleOf($y, $tile);

            self::assertTrue(
                $onVerticalEdge || $onHorizontalEdge,
                \sprintf('A quarter ends at (%s, %s), which is not the middle of a cell edge.', $x, $y),
            );
        }
    }

    /**
     * Nothing crosses an edge here: the quarters stay inside their cell. What
     * has to line up is where they stop, so the tile next door carries on from
     * the same point.
     */
    public function testEveryEndOnAnEdgeIsFacedByItsTranslate(): void
    {
        $truchet = Truchet::create(tile: 20, thickness: 2, cells: 4, seed: 1);
        $width = $truchet->tileWidth();
        $height = $truchet->tileHeight();

        $ends = $this->endsOf($truchet);
        $checked = 0;

        foreach ($ends as [$x, $y]) {
            $shifts = [];

            if (abs($x) < self::TOLERANCE) {
                $shifts[] = [$width, 0.0];
            }

            if (abs($x - $width) < self::TOLERANCE) {
                $shifts[] = [-$width, 0.0];
            }

            if (abs($y) < self::TOLERANCE) {
                $shifts[] = [0.0, $height];
            }

            if (abs($y - $height) < self::TOLERANCE) {
                $shifts[] = [0.0, -$height];
            }

            foreach ($shifts as [$dx, $dy]) {
                ++$checked;
                self::assertTrue(
                    $this->hasEnd($ends, [$x + $dx, $y + $dy]),
                    \sprintf('A quarter ends at (%s, %s) but nothing ends at (%s, %s), a period away.', $x, $y, $x + $dx, $y + $dy),
                );
            }
        }

        self::assertSame(4 * 4, $checked, 'One end per boundary cell, on the four edges.');
    }

    public function testTheCurvesAreOutlined(): void
    {
        $shapes = Shapes::of(Truchet::create(tile: 20, thickness: 2.5, cells: 4, seed: 1));

        self::assertCount(1, $shapes);
        self::assertSame('none', $shapes[0]->getAttribute('fill'));
        self::assertSame('2.5', $shapes[0]->getAttribute('stroke-width'));
    }

    public function testTheSameSeedDrawsTheSameTile(): void
    {
        $first = Truchet::create(tile: 20, thickness: 2, cells: 4, seed: 12);
        $second = Truchet::create(tile: 20, thickness: 2, cells: 4, seed: 12);

        self::assertSame(Shapes::subpaths($first), Shapes::subpaths($second));
        self::assertSame($first->id(), $second->id());
    }

    public function testTwoSeedsDrawTwoTiles(): void
    {
        $first = Truchet::create(tile: 20, thickness: 2, cells: 4, seed: 12);
        $second = Truchet::create(tile: 20, thickness: 2, cells: 4, seed: 13);

        self::assertNotSame(Shapes::subpaths($first), Shapes::subpaths($second));
        self::assertNotSame($first->id(), $second->id());
    }

    public function testALineThickerThanHalfACellIsRejected(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('thickness must not exceed half the tile (10), got 11.');

        Truchet::create(tile: 20, thickness: 11);
    }

    public function testTheCellMustBePositive(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('tile must be a finite number greater than 0, got NAN.');

        Truchet::create(tile: \NAN);
    }

    public function testThicknessMustBePositive(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('thickness must be a finite number greater than 0');

        Truchet::create(tile: 20, thickness: 0);
    }

    public function testATileOfOneCellIsRejected(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('cells must be at least 2, got 1.');

        Truchet::create(tile: 20, thickness: 2, cells: 1);
    }

    /**
     * @return list<array{0: float, 1: float}>
     */
    private function endsOf(Truchet $truchet): array
    {
        $ends = [];

        foreach (Shapes::subpaths($truchet) as $subpath) {
            $numbers = Shapes::numbersIn($subpath);

            $ends[] = [$numbers[0], $numbers[1]];
            $ends[] = [$numbers[7], $numbers[8]];
        }

        return $ends;
    }

    /**
     * @param list<array{0: float, 1: float}> $ends
     * @param array{0: float, 1: float}       $wanted
     */
    private function hasEnd(array $ends, array $wanted): bool
    {
        foreach ($ends as $end) {
            if (abs($end[0] - $wanted[0]) < self::TOLERANCE && abs($end[1] - $wanted[1]) < self::TOLERANCE) {
                return true;
            }
        }

        return false;
    }

    private function isMultipleOf(float $value, float $step): bool
    {
        return abs($value - round($value / $step) * $step) < self::TOLERANCE;
    }

    private function isMiddleOf(float $value, float $step): bool
    {
        return $this->isMultipleOf($value - $step / 2, $step);
    }
}
