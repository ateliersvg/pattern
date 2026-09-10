<?php

declare(strict_types=1);

namespace Atelier\Pattern\Tests\Tile;

use Atelier\Pattern\Exception\InvalidArgumentException;
use Atelier\Pattern\Tests\Support\Shapes;
use Atelier\Pattern\Tile\Mosaic;
use Atelier\Svg\Element\ElementInterface;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(Mosaic::class)]
final class MosaicTest extends TestCase
{
    public function testTheTileIsTheSquareItWasGiven(): void
    {
        $mosaic = Mosaic::create(size: 90, cells: 12, sites: 8);

        self::assertSame(90.0, $mosaic->tileWidth());
        self::assertSame(90.0, $mosaic->tileHeight());
    }

    public function testTheSquaresPaveTheTile(): void
    {
        $squares = Shapes::subpaths(Mosaic::create(size: 120, cells: 6, sites: 5, tones: 3, grid: false, seeds: false));

        self::assertCount(36, $squares);
    }

    public function testEverySquareIsTheSideTheCellCountGives(): void
    {
        $mosaic = Mosaic::create(size: 120, cells: 8, sites: 5, tones: 3, grid: false, seeds: false);

        foreach (Shapes::subpaths($mosaic) as $square) {
            [$x, $y, $width, $height] = Shapes::numbersIn($square);

            self::assertSame(15.0, $width);
            self::assertSame(15.0, $height);
            self::assertSame(0.0, fmod($x, 15.0));
            self::assertSame(0.0, fmod($y, 15.0));
        }
    }

    public function testThePathsComeOutPalestFirst(): void
    {
        $tones = self::tonesOf(Mosaic::create(size: 120, cells: 10, sites: 8, tones: 5, grid: false, seeds: false));

        $sorted = $tones;
        sort($sorted);

        self::assertSame($sorted, $tones);
    }

    public function testEveryToneLiesBetweenTheBounds(): void
    {
        $tones = self::tonesOf(Mosaic::create(size: 120, cells: 10, sites: 8, tones: 5, grid: false, seeds: false));

        self::assertNotEmpty($tones);

        foreach ($tones as $tone) {
            self::assertGreaterThanOrEqual(Mosaic::MIN_OPACITY, $tone);
            self::assertLessThanOrEqual(Mosaic::MAX_OPACITY, $tone);
        }
    }

    public function testTheToneCountNeverExceedsWhatWasAsked(): void
    {
        $tones = self::tonesOf(Mosaic::create(size: 120, cells: 10, sites: 9, tones: 4, grid: false, seeds: false));

        self::assertLessThanOrEqual(4, \count($tones));
        self::assertSame($tones, array_unique($tones));
    }

    public function testTheRulesAreDrawnOnlyWhenAsked(): void
    {
        self::assertSame(1, self::ruleCount(Mosaic::create(size: 120, cells: 6, sites: 5, tones: 3, grid: true, seeds: false)));
        self::assertSame(0, self::ruleCount(Mosaic::create(size: 120, cells: 6, sites: 5, tones: 3, grid: false, seeds: false)));
    }

    public function testTheSeedsAreMarkedOnlyWhenAsked(): void
    {
        self::assertNotEmpty(Shapes::circles(Mosaic::create(size: 120, cells: 6, sites: 5, tones: 3, grid: false, seeds: true)));
        self::assertEmpty(Shapes::circles(Mosaic::create(size: 120, cells: 6, sites: 5, tones: 3, grid: false, seeds: false)));
    }

    public function testEverySeedIsMarkedAtLeastOnce(): void
    {
        $dots = Shapes::circles(Mosaic::create(size: 120, cells: 6, sites: 9, tones: 3, grid: false, seeds: true));

        self::assertGreaterThanOrEqual(9, \count($dots));
    }

    public function testTheSameSeedDrawsTheSameMosaic(): void
    {
        self::assertSame(
            Shapes::subpaths(Mosaic::create(size: 120, cells: 8, sites: 7, seed: 5)),
            Shapes::subpaths(Mosaic::create(size: 120, cells: 8, sites: 7, seed: 5)),
        );
    }

    public function testTwoSeedsAreTwoMosaics(): void
    {
        self::assertNotSame(
            Shapes::subpaths(Mosaic::create(size: 120, cells: 8, sites: 7, seed: 1)),
            Shapes::subpaths(Mosaic::create(size: 120, cells: 8, sites: 7, seed: 2)),
        );
    }

    public function testSizeMustBePositive(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('size must be a finite number greater than 0, got 0.');

        Mosaic::create(size: 0);
    }

    public function testASingleColumnOfCellsIsRejected(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('cells must be at least 2, got 1.');

        Mosaic::create(size: 120, cells: 1);
    }

    public function testASingleSeedIsRejected(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('sites must be at least 2, got 1.');

        Mosaic::create(size: 120, cells: 8, sites: 1);
    }

    public function testASingleToneIsRejected(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('tones must be at least 2, got 1.');

        Mosaic::create(size: 120, cells: 8, sites: 6, tones: 1);
    }

    public function testMoreTonesThanSeedsIsRejected(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('tones must not exceed sites (3), got 5.');

        Mosaic::create(size: 120, cells: 8, sites: 3, tones: 5);
    }

    /**
     * The opacity of each tone path, in the order the tile lays them down.
     *
     * @return list<float>
     */
    private static function tonesOf(Mosaic $mosaic): array
    {
        $tones = [];

        foreach (Shapes::of($mosaic) as $shape) {
            $tone = $shape->getAttribute('fill-opacity');

            if (null !== $tone) {
                $tones[] = (float) $tone;
            }
        }

        return $tones;
    }

    /**
     * The paths carrying the rules rather than a tone.
     */
    private static function ruleCount(Mosaic $mosaic): int
    {
        return \count(array_filter(
            Shapes::of($mosaic),
            static fn (ElementInterface $shape): bool => null !== $shape->getAttribute('stroke-opacity'),
        ));
    }
}
