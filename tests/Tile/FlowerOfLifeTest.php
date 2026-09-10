<?php

declare(strict_types=1);

namespace Atelier\Pattern\Tests\Tile;

use Atelier\Pattern\Exception\InvalidArgumentException;
use Atelier\Pattern\Tests\Support\Shapes;
use Atelier\Pattern\Tests\Support\TilingAssertions;
use Atelier\Pattern\Tile\FlowerOfLife;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(FlowerOfLife::class)]
final class FlowerOfLifeTest extends TestCase
{
    use TilingAssertions;

    public function testTheTileIsTheCellOfTheTriangularLattice(): void
    {
        $flower = FlowerOfLife::create(radius: 25);

        self::assertSame(25.0, $flower->tileWidth());
        self::assertEqualsWithDelta(25 * sqrt(3), $flower->tileHeight(), 0.0001);
    }

    public function testEveryCircleIsDrawnAtTheRadiusThatIsAlsoTheSpacing(): void
    {
        foreach (Shapes::circles(FlowerOfLife::create(radius: 18)) as $circle) {
            self::assertSame(18.0, $circle['r']);
        }
    }

    /**
     * The figure in one line: a circle passes through the middles of the six
     * around it, which is what turns the overlaps into the six petal rosette.
     */
    public function testEveryCirclePassesThroughTheMiddlesOfItsNeighbours(): void
    {
        $flower = FlowerOfLife::create(radius: 25);
        $circles = Shapes::circles($flower);

        $neighbours = 0;

        foreach ($circles as $circle) {
            if (0.0 !== $circle['cx'] || 0.0 !== $circle['cy']) {
                continue;
            }

            foreach ($circles as $other) {
                $distance = hypot($other['cx'], $other['cy']);

                if ($distance > 0.0001 && $distance < 25.5) {
                    self::assertEqualsWithDelta(25.0, $distance, 0.0001);
                    ++$neighbours;
                }
            }
        }

        self::assertGreaterThanOrEqual(3, $neighbours, 'The tile carries the neighbours its own edges cut.');
    }

    public function testTheCirclesAreDrawnNotFilled(): void
    {
        foreach (Shapes::of(FlowerOfLife::create(radius: 25, thickness: 1.5)) as $shape) {
            self::assertSame('none', $shape->getAttribute('fill'));
            self::assertSame('1.5', $shape->getAttribute('stroke-width'));
        }
    }

    public function testEveryCircleCrossingAnEdgeHasItsTranslate(): void
    {
        self::assertCirclesAndRectsJoin(FlowerOfLife::create(radius: 25));
    }

    public function testALineThickerThanAnEighthOfTheRadiusIsRejected(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('thickness must not exceed an eighth of the radius (3), got 4.');

        FlowerOfLife::create(radius: 24, thickness: 4);
    }

    public function testTheRadiusMustBePositive(): void
    {
        $this->expectException(InvalidArgumentException::class);

        FlowerOfLife::create(radius: 0);
    }
}
