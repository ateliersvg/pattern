<?php

declare(strict_types=1);

namespace Atelier\Pattern\Tests\Support;

use Atelier\Pattern\Tile\Confetti;
use Atelier\Pattern\Tile\Dots;
use PHPUnit\Framework\AssertionFailedError;
use PHPUnit\Framework\Attributes\CoversNothing;
use PHPUnit\Framework\TestCase;

/**
 * The joining check has to fail on a tile that does not join, or it proves
 * nothing about the ones that do.
 */
#[CoversNothing]
final class TilingAssertionsTest extends TestCase
{
    use TilingAssertions;

    public function testItCatchesAShapeWithoutItsTranslate(): void
    {
        $this->expectException(AssertionFailedError::class);
        $this->expectExceptionMessage('A shape crosses an edge without its translate on the opposite edge.');

        self::assertCirclesAndRectsJoin(new UnjoinedDots());
    }

    public function testItPassesOnTheTileThatFixesIt(): void
    {
        self::assertCirclesAndRectsJoin(Dots::create(spacing: 16, radius: 3, stagger: 0.5));
    }

    public function testItCatchesAScatteredShapeWithoutItsTranslate(): void
    {
        $this->expectException(AssertionFailedError::class);
        $this->expectExceptionMessage('A shape crosses an edge without its translate on the opposite edge.');

        self::assertPolygonsJoin(new UnwrappedConfetti());
    }

    public function testItPassesOnTheIrregularTileThatFixesIt(): void
    {
        self::assertPolygonsJoin(Confetti::create(spacing: 20, size: 5, cells: 2, seed: 1));
    }

    public function testItCountsTheCrossingsItChecked(): void
    {
        $broken = new UnwrappedConfetti();

        self::assertSame(1, self::countEdgeCrossings(Shapes::polygons($broken), $broken->tileWidth(), $broken->tileHeight()));
    }
}
