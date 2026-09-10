<?php

declare(strict_types=1);

namespace Atelier\Pattern\Tests\Tile;

use Atelier\Pattern\Exception\InvalidArgumentException;
use Atelier\Pattern\Tests\Support\Shapes;
use Atelier\Pattern\Tests\Support\TilingAssertions;
use Atelier\Pattern\Tile\StaggeredBricks;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(StaggeredBricks::class)]
final class StaggeredBricksTest extends TestCase
{
    use TilingAssertions;

    private const float TOLERANCE = 0.0001;

    public function testTheTileIsOneBrickWideAndItsCoursesTall(): void
    {
        $bricks = StaggeredBricks::create(width: 28, height: 12, thickness: 1.2, jitter: 0.15, cells: 5, seed: 1);

        self::assertSame(28.0, $bricks->tileWidth());
        self::assertSame(60.0, $bricks->tileHeight());
    }

    public function testEveryCourseHasABedJointSpanningTheTile(): void
    {
        $cells = 4;
        $beds = array_values(array_filter(
            Shapes::rects(StaggeredBricks::create(width: 28, height: 12, thickness: 1.2, jitter: 0.2, cells: $cells, seed: 1)),
            static fn (array $rect): bool => 28.0 === $rect['width'],
        ));

        self::assertCount($cells, $beds);

        foreach ($beds as $index => $bed) {
            self::assertSame(0.0, $bed['x']);
            self::assertSame(1.2, $bed['height']);
            self::assertSame($index * 12.0, $bed['y'], 'One bed joint per course, from the top of the tile down.');
        }
    }

    public function testAHeadJointNeverLinesUpWithTheCourseBelow(): void
    {
        $width = 28.0;
        $jitter = 0.2;
        $thickness = 1.2;
        $cells = 6;
        $joints = $this->headJointsOf(StaggeredBricks::create(width: $width, height: 12, thickness: $thickness, jitter: $jitter, cells: $cells, seed: 1));

        self::assertCount($cells, $joints);

        // The last pair steps from the bottom course of one tile to the top
        // course of the next, so the seam is checked with the rest.
        foreach ($this->consecutiveGaps($joints, $width) as $course => $apart) {
            self::assertGreaterThan(
                $thickness,
                $apart,
                \sprintf('The head joint of course %d touches the one below it.', $course),
            );
        }
    }

    /**
     * The bond has to come back where it started after the courses of the tile.
     * Anchored on an exact half brick it only does so for an even count: with an
     * odd one the bottom course would land on the top course of the tile below
     * and the wall would crack in a straight line along every seam.
     */
    public function testTheBondClosesAcrossTheHorizontalSeamWhateverTheCourseCount(): void
    {
        $width = 28.0;
        $thickness = 1.2;

        foreach ([2, 3, 4, 5, 6, 7] as $cells) {
            $bricks = StaggeredBricks::create(width: $width, height: 12, thickness: $thickness, jitter: 0.1, cells: $cells, seed: 1);
            $gaps = $this->consecutiveGaps($this->headJointsOf($bricks), $width);

            self::assertGreaterThan(
                $thickness,
                $gaps[$cells - 1],
                \sprintf('With %d courses the bond does not close: the seam puts two head joints together.', $cells),
            );
        }
    }

    public function testAHeadJointStandsTheFullHeightOfItsCourse(): void
    {
        $bricks = StaggeredBricks::create(width: 28, height: 12, thickness: 1.2, jitter: 0.2, cells: 4, seed: 1);

        foreach (Shapes::rects($bricks) as $rect) {
            if (1.2 !== $rect['width']) {
                continue;
            }

            self::assertSame(12.0, $rect['height']);
            self::assertLessThanOrEqual($bricks->tileHeight(), $rect['y'] + $rect['height']);
        }
    }

    public function testEveryShapeCrossingAnEdgeHasItsTranslate(): void
    {
        self::assertCirclesAndRectsJoin(StaggeredBricks::create(width: 28, height: 12, thickness: 1.2, jitter: 0.2, cells: 4, seed: 1));
    }

    public function testAHeadJointOnTheEdgeIsDrawnAgainstBothOfThem(): void
    {
        // Seed 8 puts a head joint across the vertical edge of the tile.
        $bricks = StaggeredBricks::create(width: 28, height: 12, thickness: 1.2, jitter: 0.2, cells: 4, seed: 8);

        self::assertCount(9, Shapes::rects($bricks), 'Four bed joints, four head joints, and the one drawn twice.');
        self::assertCirclesAndRectsJoin($bricks);
    }

    public function testTheSameSeedDrawsTheSameTile(): void
    {
        $first = StaggeredBricks::create(seed: 12);
        $second = StaggeredBricks::create(seed: 12);

        self::assertSame(Shapes::rects($first), Shapes::rects($second));
        self::assertSame($first->id(), $second->id());
    }

    public function testTwoSeedsDrawTwoTiles(): void
    {
        $first = StaggeredBricks::create(seed: 12);
        $second = StaggeredBricks::create(seed: 13);

        self::assertNotSame(Shapes::rects($first), Shapes::rects($second));
        self::assertNotSame($first->id(), $second->id());
    }

    public function testAJointWiderThanTheBrickIsRejected(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('thickness must not exceed width (10), got 11.');

        StaggeredBricks::create(width: 10, height: 20, thickness: 11);
    }

    public function testAJointTallerThanTheCourseIsRejected(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('thickness must not exceed height (12), got 13.');

        StaggeredBricks::create(width: 28, height: 12, thickness: 13);
    }

    public function testWidthMustBePositive(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('width must be a finite number greater than 0');

        StaggeredBricks::create(width: 0);
    }

    public function testHeightMustBePositive(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('height must be a finite number greater than 0');

        StaggeredBricks::create(width: 28, height: -1);
    }

    public function testThicknessMustBePositive(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('thickness must be a finite number greater than 0');

        StaggeredBricks::create(width: 28, height: 12, thickness: 0);
    }

    public function testAJitterThatWouldPutTwoHeadJointsTogetherIsRejected(): void
    {
        // Half a brick of bond, less the joint that has to fit between two of
        // them, shared by the two courses that each wobble.
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('jitter must be between 0 and 0.2286, got 0.3.');

        StaggeredBricks::create(width: 28, height: 12, thickness: 1.2, jitter: 0.3);
    }

    public function testTheJitterLimitFollowsTheBondStep(): void
    {
        // Five courses cannot step by half a brick and close, so they step by
        // two fifths and the room left for the wobble shrinks with it.
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('jitter must be between 0 and 0.1786, got 0.2.');

        StaggeredBricks::create(width: 28, height: 12, thickness: 1.2, jitter: 0.2, cells: 5);
    }

    public function testAJointTooWideToLeaveAnyRoomAllowsNoJitterAtAll(): void
    {
        self::assertSame(0.0, StaggeredBricks::create(width: 28, height: 24, thickness: 20, jitter: 0)->tileWidth() - 28.0);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('jitter must be between 0 and 0, got 0.01.');

        StaggeredBricks::create(width: 28, height: 24, thickness: 20, jitter: 0.01);
    }

    public function testAWallOfOneCourseIsRejected(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('cells must be at least 2, got 1.');

        StaggeredBricks::create(width: 28, height: 12, thickness: 1.2, jitter: 0.2, cells: 1);
    }

    /**
     * The distance from each head joint to the one in the next course, folded
     * into the brick. The last entry steps across the horizontal seam, from the
     * bottom course to the top course of the tile below.
     *
     * @param list<float> $joints
     *
     * @return list<float>
     */
    private function consecutiveGaps(array $joints, float $width): array
    {
        $gaps = [];
        $count = \count($joints);

        for ($course = 0; $course < $count; ++$course) {
            $apart = abs($joints[($course + 1) % $count] - $joints[$course]);
            $gaps[] = min($apart, $width - $apart);
        }

        return $gaps;
    }

    /**
     * Where the head joint of each course stands, in the order the courses come.
     *
     * @return list<float>
     */
    private function headJointsOf(StaggeredBricks $bricks): array
    {
        $joints = [];

        foreach (Shapes::rects($bricks) as $rect) {
            if ($rect['width'] === $bricks->tileWidth()) {
                continue;
            }

            $joints[(string) $rect['y']] ??= $rect['x'];
        }

        return array_values($joints);
    }
}
