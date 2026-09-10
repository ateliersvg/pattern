<?php

declare(strict_types=1);

namespace Atelier\Pattern\Tests;

use Atelier\Pattern\Pattern;
use Atelier\Pattern\Tile\Asanoha;
use Atelier\Pattern\Tile\Checker;
use Atelier\Pattern\Tile\Chevron;
use Atelier\Pattern\Tile\Confetti;
use Atelier\Pattern\Tile\Crosshatch;
use Atelier\Pattern\Tile\Dots;
use Atelier\Pattern\Tile\FlowerOfLife;
use Atelier\Pattern\Tile\GreekKey;
use Atelier\Pattern\Tile\Grid;
use Atelier\Pattern\Tile\Herringbone;
use Atelier\Pattern\Tile\Honeycomb;
use Atelier\Pattern\Tile\Houndstooth;
use Atelier\Pattern\Tile\IsometricCubes;
use Atelier\Pattern\Tile\JitteredDots;
use Atelier\Pattern\Tile\Mosaic;
use Atelier\Pattern\Tile\Quatrefoil;
use Atelier\Pattern\Tile\RoughHatch;
use Atelier\Pattern\Tile\Scales;
use Atelier\Pattern\Tile\Seigaiha;
use Atelier\Pattern\Tile\StaggeredBricks;
use Atelier\Pattern\Tile\Stripes;
use Atelier\Pattern\Tile\Triangles;
use Atelier\Pattern\Tile\Truchet;
use Atelier\Pattern\Tile\Voronoi;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

#[CoversClass(Pattern::class)]
final class PatternTest extends TestCase
{
    /**
     * @return iterable<string, array{callable(): object, class-string}>
     */
    public static function factories(): iterable
    {
        yield 'mosaic' => [Pattern::mosaic(...), Mosaic::class];
        yield 'triangles' => [Pattern::triangles(...), Triangles::class];
        yield 'herringbone' => [Pattern::herringbone(...), Herringbone::class];
        yield 'seigaiha' => [Pattern::seigaiha(...), Seigaiha::class];
        yield 'flowerOfLife' => [Pattern::flowerOfLife(...), FlowerOfLife::class];
        yield 'quatrefoil' => [Pattern::quatrefoil(...), Quatrefoil::class];
        yield 'greekKey' => [Pattern::greekKey(...), GreekKey::class];
        yield 'dots' => [Pattern::dots(...), Dots::class];
        yield 'stripes' => [Pattern::stripes(...), Stripes::class];
        yield 'crosshatch' => [Pattern::crosshatch(...), Crosshatch::class];
        yield 'grid' => [Pattern::grid(...), Grid::class];
        yield 'honeycomb' => [Pattern::honeycomb(...), Honeycomb::class];
        yield 'scales' => [Pattern::scales(...), Scales::class];
        yield 'chevron' => [Pattern::chevron(...), Chevron::class];
        yield 'checker' => [Pattern::checker(...), Checker::class];
        yield 'truchet' => [Pattern::truchet(...), Truchet::class];
        yield 'jitteredDots' => [Pattern::jitteredDots(...), JitteredDots::class];
        yield 'roughHatch' => [Pattern::roughHatch(...), RoughHatch::class];
        yield 'staggeredBricks' => [Pattern::staggeredBricks(...), StaggeredBricks::class];
        yield 'confetti' => [Pattern::confetti(...), Confetti::class];
        yield 'voronoi' => [Pattern::voronoi(...), Voronoi::class];
        yield 'houndstooth' => [Pattern::houndstooth(...), Houndstooth::class];
        yield 'isometricCubes' => [Pattern::isometricCubes(...), IsometricCubes::class];
        yield 'asanoha' => [Pattern::asanoha(...), Asanoha::class];
    }

    /**
     * @param callable(): object $factory
     * @param class-string       $expected
     */
    #[DataProvider('factories')]
    public function testFactoryBuildsItsTile(callable $factory, string $expected): void
    {
        self::assertInstanceOf($expected, $factory());
    }

    public function testFactoriesForwardTheirGeometry(): void
    {
        self::assertSame(21.0, Pattern::dots(spacing: 21)->tileWidth());
        self::assertSame(42.0, Pattern::dots(spacing: 21, stagger: 0.5)->tileHeight());
        self::assertSame(9.0, Pattern::stripes(spacing: 9)->tileWidth());
        self::assertSame(9.0, Pattern::crosshatch(spacing: 9)->tileHeight());
        self::assertSame(30.0, Pattern::grid(size: 30)->tileWidth());
        self::assertSame(30.0, Pattern::honeycomb(radius: 10)->tileHeight());
        self::assertSame(26.0, Pattern::scales(height: 13)->tileHeight());
        self::assertSame(8.0, Pattern::chevron(size: 16)->tileHeight());
        self::assertSame(16.0, Pattern::checker(size: 8)->tileWidth());
        self::assertSame(60.0, Pattern::truchet(tile: 20, cells: 3)->tileWidth());
        self::assertSame(45.0, Pattern::jitteredDots(spacing: 15, cells: 3)->tileHeight());
        self::assertSame(40.0, Pattern::roughHatch(spacing: 10, cells: 4)->tileWidth());
        self::assertSame(33.0, Pattern::staggeredBricks(width: 33)->tileWidth());
        self::assertSame(50.0, Pattern::confetti(spacing: 25, cells: 2)->tileHeight());
        self::assertSame(80.0, Pattern::voronoi(size: 80)->tileWidth());
        self::assertSame(36.0, Pattern::houndstooth(size: 36)->tileHeight());
        self::assertSame(45.0, Pattern::isometricCubes(size: 15)->tileHeight());
        self::assertSame(60.0, Pattern::asanoha(size: 20)->tileHeight());
    }

    public function testTheSeedIsPartOfTheIdentifier(): void
    {
        self::assertNotSame(Pattern::truchet(seed: 1)->id(), Pattern::truchet(seed: 2)->id());
        self::assertNotSame(Pattern::jitteredDots(seed: 1)->id(), Pattern::jitteredDots(seed: 2)->id());
        self::assertNotSame(Pattern::roughHatch(seed: 1)->id(), Pattern::roughHatch(seed: 2)->id());
        self::assertNotSame(Pattern::staggeredBricks(seed: 1)->id(), Pattern::staggeredBricks(seed: 2)->id());
        self::assertNotSame(Pattern::confetti(seed: 1)->id(), Pattern::confetti(seed: 2)->id());
        self::assertNotSame(Pattern::voronoi(seed: 1)->id(), Pattern::voronoi(seed: 2)->id());
    }
}
