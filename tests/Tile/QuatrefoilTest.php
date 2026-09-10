<?php

declare(strict_types=1);

namespace Atelier\Pattern\Tests\Tile;

use Atelier\Pattern\Exception\InvalidArgumentException;
use Atelier\Pattern\Tests\Support\Shapes;
use Atelier\Pattern\Tests\Support\TilingAssertions;
use Atelier\Pattern\Tile\Quatrefoil;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(Quatrefoil::class)]
final class QuatrefoilTest extends TestCase
{
    use TilingAssertions;

    public function testTheTileIsTheLatticeCell(): void
    {
        $quatrefoil = Quatrefoil::create(size: 34);

        self::assertSame(34.0, $quatrefoil->tileWidth());
        self::assertSame(34.0, $quatrefoil->tileHeight());
    }

    public function testTheTileCarriesTwoRosettesOfFourLobes(): void
    {
        $inside = 0;

        foreach (Shapes::circles(Quatrefoil::create(size: 34, lobe: 6.4)) as $lobe) {
            if ($lobe['cx'] >= 0 && $lobe['cx'] < 34 && $lobe['cy'] >= 0 && $lobe['cy'] < 34) {
                ++$inside;
            }
        }

        self::assertSame(8, $inside);
    }

    public function testEveryLobeStandsOnAnArmOfItsRosette(): void
    {
        $lobes = Shapes::circles(Quatrefoil::create(size: 34, lobe: 6.4));

        foreach ([[0.0, 0.0], [17.0, 17.0]] as [$cx, $cy]) {
            $arms = [];

            foreach ($lobes as $lobe) {
                $dx = $lobe['cx'] - $cx;
                $dy = $lobe['cy'] - $cy;

                if (hypot($dx, $dy) > 8.0) {
                    continue;
                }

                // One coordinate carries the whole offset: the four lobes sit
                // on a cross, never on a diagonal.
                self::assertTrue(abs($dx) < 0.0001 || abs($dy) < 0.0001);
                $arms[] = [round($dx, 4), round($dy, 4)];
            }

            self::assertCount(4, $arms);
        }
    }

    public function testEveryLobeIsDrawnAtTheRadiusItWasGiven(): void
    {
        foreach (Shapes::circles(Quatrefoil::create(size: 40, lobe: 7)) as $lobe) {
            self::assertSame(7.0, $lobe['r']);
        }
    }

    public function testEveryLobeCrossingAnEdgeHasItsTranslate(): void
    {
        self::assertCirclesAndRectsJoin(Quatrefoil::create(size: 34, lobe: 6.4));
    }

    public function testALobeLargerThanAQuarterOfTheCellIsRejected(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('lobe must not exceed a quarter of the size (8.5), got 9.');

        Quatrefoil::create(size: 34, lobe: 9);
    }

    public function testSizeMustBePositive(): void
    {
        $this->expectException(InvalidArgumentException::class);

        Quatrefoil::create(size: 0);
    }
}
