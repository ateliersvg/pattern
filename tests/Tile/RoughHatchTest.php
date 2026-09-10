<?php

declare(strict_types=1);

namespace Atelier\Pattern\Tests\Tile;

use Atelier\Pattern\Exception\InvalidArgumentException;
use Atelier\Pattern\Tests\Support\Shapes;
use Atelier\Pattern\Tests\Support\TilingAssertions;
use Atelier\Pattern\Tile\RoughHatch;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(RoughHatch::class)]
final class RoughHatchTest extends TestCase
{
    use TilingAssertions;

    private const float TOLERANCE = 0.0001;

    public function testTheTileHoldsAsManyStrokesAsAsked(): void
    {
        $hatch = RoughHatch::create(spacing: 14, thickness: 1.4, jitter: 0.25, cells: 5, seed: 1);

        self::assertSame(70.0, $hatch->tileWidth());
        self::assertSame(70.0, $hatch->tileHeight());
    }

    public function testEveryStrokeRunsFromOneHorizontalEdgeToTheOther(): void
    {
        $hatch = RoughHatch::create(spacing: 14, thickness: 1.4, jitter: 0.25, cells: 6, seed: 1);
        $height = $hatch->tileHeight();

        foreach ($this->strokesOf($hatch) as $points) {
            $first = $points[0];
            $last = $points[\count($points) - 1];

            self::assertSame(0.0, $first[1], 'A stroke starts on the top edge.');
            self::assertEqualsWithDelta($height, $last[1], self::TOLERANCE, 'A stroke ends on the bottom edge.');
            self::assertEqualsWithDelta($first[0], $last[0], self::TOLERANCE, 'Both ends of a stroke share one abscissa, so the tiles above and below carry on from the same point.');
        }
    }

    public function testTheNodesOfAStrokeGoDown(): void
    {
        foreach ($this->strokesOf(RoughHatch::create(spacing: 14, thickness: 1.4, jitter: 0.25, cells: 6, seed: 1)) as $points) {
            $ordinates = array_column($points, 1);
            $sorted = $ordinates;
            sort($sorted);

            self::assertSame($sorted, $ordinates, 'A stroke never doubles back on itself.');
        }
    }

    public function testEveryStrokeCrossingAVerticalEdgeHasItsTranslate(): void
    {
        $hatch = RoughHatch::create(spacing: 14, thickness: 1.4, jitter: 0.25, cells: 6, seed: 1);
        $strokes = $this->strokesOf($hatch);
        $margin = 1.4 / 2;

        self::assertGreaterThan(
            0,
            self::countEdgeCrossings($strokes, $hatch->tileWidth(), $hatch->tileHeight(), $margin),
            'No stroke crosses an edge, so nothing was checked.',
        );

        self::assertPointListsJoin($strokes, $hatch->tileWidth(), $hatch->tileHeight(), 'stroke', $margin);
    }

    public function testTheEndsAreRoundedSoTheyCloseOverTheSeam(): void
    {
        $shapes = Shapes::of(RoughHatch::create(spacing: 14, thickness: 1.4, jitter: 0.25, cells: 6, seed: 1));

        self::assertCount(1, $shapes);
        self::assertSame('round', $shapes[0]->getAttribute('stroke-linecap'));
        self::assertSame('round', $shapes[0]->getAttribute('stroke-linejoin'));
    }

    public function testTheStrokesAreOutlined(): void
    {
        $shapes = Shapes::of(RoughHatch::create(spacing: 14, thickness: 1.6, jitter: 0.25, cells: 6, seed: 1));

        self::assertSame('none', $shapes[0]->getAttribute('fill'));
        self::assertSame('1.6', $shapes[0]->getAttribute('stroke-width'));
    }

    public function testTheSameSeedDrawsTheSameTile(): void
    {
        $first = RoughHatch::create(seed: 12);
        $second = RoughHatch::create(seed: 12);

        self::assertSame(Shapes::subpaths($first), Shapes::subpaths($second));
        self::assertSame($first->id(), $second->id());
    }

    public function testTwoSeedsDrawTwoTiles(): void
    {
        $first = RoughHatch::create(seed: 12);
        $second = RoughHatch::create(seed: 13);

        self::assertNotSame(Shapes::subpaths($first), Shapes::subpaths($second));
        self::assertNotSame($first->id(), $second->id());
    }

    public function testALineThickerThanTheSpacingIsRejected(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('thickness must not exceed spacing (14), got 15.');

        RoughHatch::create(spacing: 14, thickness: 15);
    }

    public function testSpacingMustBePositive(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('spacing must be a finite number greater than 0, got INF.');

        RoughHatch::create(spacing: \INF);
    }

    public function testThicknessMustBePositive(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('thickness must be a finite number greater than 0');

        RoughHatch::create(spacing: 14, thickness: 0);
    }

    public function testAJitterBeyondAQuarterOfASpacingIsRejected(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('jitter must be between 0 and 0.25, got 0.3.');

        RoughHatch::create(spacing: 14, thickness: 1.4, jitter: 0.3);
    }

    public function testATileOfOneStrokeIsRejected(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('cells must be at least 2, got 1.');

        RoughHatch::create(spacing: 14, thickness: 1.4, jitter: 0.25, cells: 1);
    }

    /**
     * @return list<list<array{0: float, 1: float}>>
     */
    private function strokesOf(RoughHatch $hatch): array
    {
        return array_map(Shapes::pointsIn(...), Shapes::subpaths($hatch));
    }
}
