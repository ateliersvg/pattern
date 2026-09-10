<?php

declare(strict_types=1);

namespace Atelier\Pattern\Tests\Tile;

use Atelier\Pattern\Exception\InvalidArgumentException;
use Atelier\Pattern\Tests\Support\Shapes;
use Atelier\Pattern\Tests\Support\TilingAssertions;
use Atelier\Pattern\Tile\Herringbone;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(Herringbone::class)]
final class HerringboneTest extends TestCase
{
    use TilingAssertions;

    public function testTheTileIsTwoBricksSquare(): void
    {
        $herringbone = Herringbone::create(length: 24);

        self::assertSame(48.0, $herringbone->tileWidth());
        self::assertSame(48.0, $herringbone->tileHeight());
    }

    public function testEveryBrickIsTwiceAsLongAsItIsWide(): void
    {
        foreach (Shapes::rects(Herringbone::create(length: 24, gap: 0)) as $brick) {
            $long = max($brick['width'], $brick['height']);
            $short = min($brick['width'], $brick['height']);

            self::assertEqualsWithDelta(24.0, $long, 0.0001);
            self::assertEqualsWithDelta(12.0, $short, 0.0001);
        }
    }

    public function testHalfTheBricksAreTurnedAgainstTheOther(): void
    {
        $lying = 0;
        $standing = 0;

        foreach (Shapes::rects(Herringbone::create(length: 24, gap: 0)) as $brick) {
            // The copies an edge calls for are counted with the tile they
            // belong to, so only the bricks laid inside it are counted here.
            if ($brick['x'] < 0.0 || $brick['x'] >= 48.0 || $brick['y'] < 0.0 || $brick['y'] >= 48.0) {
                continue;
            }

            $brick['width'] > $brick['height'] ? ++$lying : ++$standing;
        }

        self::assertSame(4, $lying);
        self::assertSame(4, $standing);
    }

    /**
     * The claim the whole construction rests on: four pairs at the residues of
     * the lattice cover the tile once, with nothing over and nothing missing.
     */
    public function testTheBondCoversEveryPointOfTheTileExactlyOnce(): void
    {
        $herringbone = Herringbone::create(length: 24, gap: 0);
        $bricks = Shapes::rects($herringbone);

        for ($row = 0; $row < 24; ++$row) {
            for ($column = 0; $column < 24; ++$column) {
                $x = ($column + 0.5) * $herringbone->tileWidth() / 24;
                $y = ($row + 0.5) * $herringbone->tileHeight() / 24;

                $covers = 0;

                foreach ($bricks as $brick) {
                    if ($x > $brick['x'] && $x < $brick['x'] + $brick['width']
                        && $y > $brick['y'] && $y < $brick['y'] + $brick['height']) {
                        ++$covers;
                    }
                }

                self::assertSame(1, $covers, \sprintf('The point (%s, %s) is covered %d times.', $x, $y, $covers));
            }
        }
    }

    public function testTheGapIsTakenOutOfTheBrickRatherThanAddedAroundIt(): void
    {
        $bricks = Shapes::rects(Herringbone::create(length: 24, gap: 2));

        foreach ($bricks as $brick) {
            self::assertEqualsWithDelta(22.0, max($brick['width'], $brick['height']), 0.0001);
            self::assertEqualsWithDelta(10.0, min($brick['width'], $brick['height']), 0.0001);
        }
    }

    public function testEveryBrickCrossingAnEdgeHasItsTranslate(): void
    {
        self::assertCirclesAndRectsJoin(Herringbone::create(length: 24, gap: 1.6));
    }

    public function testLengthMustBePositive(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('length must be a finite number greater than 0, got 0.');

        Herringbone::create(length: 0);
    }

    public function testAGapWiderThanAQuarterOfTheLengthIsRejected(): void
    {
        $this->expectException(InvalidArgumentException::class);

        Herringbone::create(length: 24, gap: 7);
    }
}
