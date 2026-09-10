<?php

declare(strict_types=1);

namespace Atelier\Pattern\Tests\Tile;

use Atelier\Pattern\Exception\InvalidArgumentException;
use Atelier\Pattern\Tests\Support\Shapes;
use Atelier\Pattern\Tile\Scales;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(Scales::class)]
final class ScalesTest extends TestCase
{
    public function testTileSpansTwoRows(): void
    {
        $scales = Scales::create(width: 20, height: 12, thickness: 1);

        self::assertSame(20.0, $scales->tileWidth());
        self::assertSame(24.0, $scales->tileHeight());
    }

    public function testTheAlignedRowSpansTheTileFromEdgeToEdge(): void
    {
        $subpaths = Shapes::subpaths(Scales::create(width: 20, height: 12, thickness: 1));

        self::assertSame('M 0 18 A 10 12 0 0 1 20 18', $subpaths[0]);
    }

    public function testTheShiftedRowIsDrawnAgainstBothVerticalEdges(): void
    {
        $subpaths = Shapes::subpaths(Scales::create(width: 20, height: 12, thickness: 1));

        self::assertContains('M -10 6 A 10 12 0 0 1 10 6', $subpaths);
        self::assertContains('M 10 6 A 10 12 0 0 1 30 6', $subpaths);
    }

    public function testTheShiftedRowIsRepeatedOneFullPeriodDown(): void
    {
        $scales = Scales::create(width: 20, height: 12, thickness: 1);
        $period = $scales->tileHeight();
        $subpaths = Shapes::subpaths($scales);

        self::assertCount(5, $subpaths);

        $shifted = array_values(array_filter(
            $subpaths,
            static fn (string $subpath): bool => 6.0 === Shapes::numbersIn($subpath)[1],
        ));

        self::assertCount(2, $shifted);

        foreach ($shifted as $subpath) {
            $numbers = Shapes::numbersIn($subpath);
            $translated = \sprintf(
                'M %s %s A %s %s 0 0 1 %s %s',
                $numbers[0],
                $numbers[1] + $period,
                $numbers[2],
                $numbers[3],
                $numbers[7],
                $numbers[8] + $period,
            );

            self::assertContains($translated, $subpaths, 'The shifted row crosses an edge but its translate a period away is missing.');
        }
    }

    public function testAnArcRisesOneHeightOverOneWidth(): void
    {
        foreach (Shapes::subpaths(Scales::create(width: 20, height: 12, thickness: 1)) as $subpath) {
            $numbers = Shapes::numbersIn($subpath);

            self::assertSame(10.0, $numbers[2], 'Half the width is the horizontal radius.');
            self::assertSame(12.0, $numbers[3], 'The height is the vertical radius.');
            self::assertSame(20.0, $numbers[7] - $numbers[0], 'An arc spans one width.');
            self::assertSame($numbers[1], $numbers[8], 'An arc starts and ends on the same baseline.');
        }
    }

    public function testTheArcsAreOutlined(): void
    {
        $shapes = Shapes::of(Scales::create(width: 20, height: 12, thickness: 1.5));

        self::assertCount(1, $shapes);
        self::assertSame('none', $shapes[0]->getAttribute('fill'));
        self::assertSame('1.5', $shapes[0]->getAttribute('stroke-width'));
    }

    public function testALineThickerThanTheHeightIsRejected(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('thickness must not exceed height (12), got 13.');

        Scales::create(width: 20, height: 12, thickness: 13);
    }

    public function testWidthMustBePositive(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('width must be a finite number greater than 0');

        Scales::create(width: 0);
    }

    public function testHeightMustBePositive(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('height must be a finite number greater than 0');

        Scales::create(width: 20, height: -1);
    }

    public function testThicknessMustBePositive(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('thickness must be a finite number greater than 0');

        Scales::create(width: 20, height: 12, thickness: 0);
    }
}
