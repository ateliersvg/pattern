<?php

declare(strict_types=1);

namespace Atelier\Pattern\Tests\Tile;

use Atelier\Pattern\Exception\InvalidArgumentException;
use Atelier\Pattern\Tests\Support\Shapes;
use Atelier\Pattern\Tile\Chevron;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(Chevron::class)]
final class ChevronTest extends TestCase
{
    public function testTileIsHalfAsTallAsItIsWide(): void
    {
        $chevron = Chevron::create(size: 20, thickness: 2);

        self::assertSame(20.0, $chevron->tileWidth());
        self::assertSame(10.0, $chevron->tileHeight());
    }

    public function testTheRowsAboveAndBelowAreDrawnToo(): void
    {
        $subpaths = Shapes::subpaths(Chevron::create(size: 20, thickness: 2));

        self::assertSame([
            'M -10 0 L 0 -10 L 10 0 L 20 -10 L 30 0',
            'M -10 10 L 0 0 L 10 10 L 20 0 L 30 10',
            'M -10 20 L 0 10 L 10 20 L 20 10 L 30 20',
        ], $subpaths);
    }

    public function testTheThreeRowsAreOnePeriodApart(): void
    {
        $chevron = Chevron::create(size: 20, thickness: 2);
        $period = $chevron->tileHeight();

        $baselines = array_map(
            static fn (string $subpath): float => Shapes::numbersIn($subpath)[3],
            Shapes::subpaths($chevron),
        );

        self::assertSame([-$period, 0.0, $period], $baselines);
    }

    public function testEachZigzagRunsHalfAPeriodPastBothVerticalEdges(): void
    {
        $chevron = Chevron::create(size: 20, thickness: 2);
        $overhang = $chevron->tileWidth() / 2;

        foreach (Shapes::subpaths($chevron) as $subpath) {
            $xs = [];

            foreach (array_chunk(Shapes::numbersIn($subpath), 2) as [$x]) {
                $xs[] = $x;
            }

            self::assertSame(-$overhang, min($xs), 'The zigzag stops short of the left edge, so its corner is capped instead of mitred.');
            self::assertSame($chevron->tileWidth() + $overhang, max($xs), 'The zigzag stops short of the right edge, so its corner is capped instead of mitred.');
        }
    }

    public function testPeaksAndTroughsAlternateByHalfASize(): void
    {
        $subpaths = Shapes::subpaths(Chevron::create(size: 20, thickness: 2));
        $numbers = Shapes::numbersIn($subpaths[1]);

        self::assertSame([-10.0, 10.0, 0.0, 0.0, 10.0, 10.0, 20.0, 0.0, 30.0, 10.0], $numbers);
    }

    public function testTheZigzagIsOutlined(): void
    {
        $shapes = Shapes::of(Chevron::create(size: 20, thickness: 2));

        self::assertCount(1, $shapes);
        self::assertSame('none', $shapes[0]->getAttribute('fill'));
        self::assertSame('2', $shapes[0]->getAttribute('stroke-width'));
    }

    public function testALineThickerThanHalfTheSizeIsRejected(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('thickness must not exceed half the size (10), got 11.');

        Chevron::create(size: 20, thickness: 11);
    }

    public function testSizeMustBePositive(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('size must be a finite number greater than 0');

        Chevron::create(size: 0);
    }

    public function testThicknessMustBePositive(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('thickness must be a finite number greater than 0');

        Chevron::create(size: 20, thickness: -2);
    }
}
