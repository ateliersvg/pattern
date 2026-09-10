<?php

declare(strict_types=1);

namespace Atelier\Pattern\Tests;

use Atelier\Pattern\AbstractPattern;
use Atelier\Pattern\Pattern;
use Atelier\Pattern\PatternRegistry;
use Atelier\Pattern\Tests\Support\TilingAssertions;
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

/**
 * What every tile of the catalogue owes, whatever it draws.
 */
#[CoversClass(Asanoha::class)]
#[CoversClass(Checker::class)]
#[CoversClass(Chevron::class)]
#[CoversClass(Confetti::class)]
#[CoversClass(Crosshatch::class)]
#[CoversClass(Dots::class)]
#[CoversClass(FlowerOfLife::class)]
#[CoversClass(GreekKey::class)]
#[CoversClass(Grid::class)]
#[CoversClass(Herringbone::class)]
#[CoversClass(Honeycomb::class)]
#[CoversClass(Houndstooth::class)]
#[CoversClass(IsometricCubes::class)]
#[CoversClass(JitteredDots::class)]
#[CoversClass(Quatrefoil::class)]
#[CoversClass(RoughHatch::class)]
#[CoversClass(Scales::class)]
#[CoversClass(Seigaiha::class)]
#[CoversClass(StaggeredBricks::class)]
#[CoversClass(Stripes::class)]
#[CoversClass(Triangles::class)]
#[CoversClass(Truchet::class)]
#[CoversClass(Voronoi::class)]
final class CatalogueTest extends TestCase
{
    use TilingAssertions;

    /**
     * @return iterable<string, array{AbstractPattern}>
     */
    public static function catalogue(): iterable
    {
        yield 'dots' => [Pattern::dots()];
        yield 'dots, staggered' => [Pattern::dots(stagger: 0.5)];
        yield 'dots, on the grid nodes' => [Pattern::dots(phase: 0.5)];
        yield 'stripes' => [Pattern::stripes()];
        yield 'stripes, angled' => [Pattern::stripes(angle: 45)];
        yield 'crosshatch' => [Pattern::crosshatch()];
        yield 'grid' => [Pattern::grid()];
        yield 'honeycomb' => [Pattern::honeycomb()];
        yield 'scales' => [Pattern::scales()];
        yield 'chevron' => [Pattern::chevron()];
        yield 'checker' => [Pattern::checker()];
        yield 'truchet' => [Pattern::truchet()];
        yield 'jitteredDots' => [Pattern::jitteredDots()];
        yield 'roughHatch' => [Pattern::roughHatch()];
        yield 'staggeredBricks' => [Pattern::staggeredBricks()];
        yield 'confetti' => [Pattern::confetti()];
        yield 'voronoi' => [Pattern::voronoi()];
        yield 'voronoi, filled' => [Pattern::voronoi(filled: true)];
        yield 'houndstooth' => [Pattern::houndstooth()];
        yield 'isometricCubes' => [Pattern::isometricCubes()];
        yield 'asanoha' => [Pattern::asanoha()];
        yield 'mosaic' => [Pattern::mosaic()];
        yield 'triangles' => [Pattern::triangles()];
        yield 'triangles, one tone' => [Pattern::triangles(down: 1.0)];
        yield 'herringbone' => [Pattern::herringbone()];
        yield 'quatrefoil' => [Pattern::quatrefoil()];
        yield 'seigaiha' => [Pattern::seigaiha()];
        yield 'greekKey' => [Pattern::greekKey()];
        yield 'flowerOfLife' => [Pattern::flowerOfLife()];
    }

    #[DataProvider('catalogue')]
    public function testTileIsMeasuredInUserSpace(AbstractPattern $pattern): void
    {
        $element = $pattern->element();

        self::assertSame('userSpaceOnUse', $element->getPatternUnits());
        self::assertGreaterThan(0.0, $pattern->tileWidth());
        self::assertGreaterThan(0.0, $pattern->tileHeight());
        self::assertSame((string) round($pattern->tileWidth(), 4), $element->getAttribute('width'));
        self::assertSame((string) round($pattern->tileHeight(), 4), $element->getAttribute('height'));
    }

    #[DataProvider('catalogue')]
    public function testTileDrawsSomething(AbstractPattern $pattern): void
    {
        self::assertNotEmpty($pattern->element()->getChildren());
    }

    #[DataProvider('catalogue')]
    public function testTileLeavesItsBackgroundToTheSurface(AbstractPattern $pattern): void
    {
        self::assertNull($pattern->element()->getAttribute('fill'));
    }

    #[DataProvider('catalogue')]
    public function testTileInheritsTheColorOfItsHost(AbstractPattern $pattern): void
    {
        $markup = $this->markupOf($pattern);

        self::assertStringContainsString('currentColor', $markup);
    }

    #[DataProvider('catalogue')]
    public function testTileTakesItsOpacityOnlyWhenAsked(AbstractPattern $pattern): void
    {
        self::assertNull($this->askedOpacityOf($pattern));
        self::assertSame('0.5', $this->askedOpacityOf($pattern->withOpacity(0.5)));
    }

    #[DataProvider('catalogue')]
    public function testEveryShapeCrossingAnEdgeHasItsTranslate(AbstractPattern $pattern): void
    {
        self::assertCirclesAndRectsJoin($pattern);
        self::assertPolygonsJoin($pattern);
    }

    #[DataProvider('catalogue')]
    public function testTileIsBuiltWithoutSideEffects(AbstractPattern $pattern): void
    {
        $before = $this->markupOf($pattern);
        $pattern->element();

        self::assertSame($before, $this->markupOf($pattern));
    }

    public function testTheCatalogueHasNoCollidingIdentifiers(): void
    {
        $registry = new PatternRegistry();

        foreach (self::catalogue() as [$pattern]) {
            $registry->add($pattern);
        }

        self::assertCount(29, $registry);
    }

    /**
     * The opacity withOpacity() puts on the tile, read off the group that
     * carries it. Ink a tile lays on its own shapes, as the cube faces do, is
     * geometry and is none of this test's business.
     */
    private function askedOpacityOf(AbstractPattern $pattern): ?string
    {
        foreach ($pattern->element()->getChildren() as $child) {
            if ('g' === $child->getTagName()) {
                return $child->getAttribute('opacity');
            }
        }

        return null;
    }

    private function markupOf(AbstractPattern $pattern): string
    {
        $parts = [];

        foreach ($pattern->element()->getChildren() as $child) {
            foreach ($child->getAttributes() as $name => $value) {
                $parts[] = $name.'="'.$value.'"';
            }

            if ($child instanceof \Atelier\Svg\Element\ContainerElementInterface) {
                foreach ($child->getChildren() as $grandchild) {
                    foreach ($grandchild->getAttributes() as $name => $value) {
                        $parts[] = $name.'="'.$value.'"';
                    }
                }
            }
        }

        return implode(' ', $parts);
    }
}
