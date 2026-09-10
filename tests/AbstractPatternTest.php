<?php

declare(strict_types=1);

namespace Atelier\Pattern\Tests;

use Atelier\Pattern\AbstractPattern;
use Atelier\Pattern\Exception\InvalidArgumentException;
use Atelier\Pattern\Pattern;
use Atelier\Pattern\Tests\Support\Shapes;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

#[CoversClass(AbstractPattern::class)]
final class AbstractPatternTest extends TestCase
{
    public function testTileIsBuiltWithoutADocument(): void
    {
        $element = Pattern::dots()->element();

        self::assertSame('pattern', $element->getTagName());
        self::assertNull($element->getParent());
    }

    public function testTileUsesUserSpace(): void
    {
        $element = Pattern::grid(size: 20, thickness: 2)->element();

        self::assertSame('userSpaceOnUse', $element->getPatternUnits());
        self::assertSame('20', $element->getAttribute('width'));
        self::assertSame('20', $element->getAttribute('height'));
    }

    public function testColorDefaultsToCurrentColor(): void
    {
        $pattern = Pattern::dots();

        self::assertSame('currentColor', $pattern->color());
        self::assertSame('currentColor', $pattern->element()->getChildren()[0]->getAttribute('fill'));
    }

    public function testBackgroundIsLeftToTheSurface(): void
    {
        $children = Pattern::dots()->element()->getChildren();

        self::assertCount(1, $children);
        self::assertSame('circle', $children[0]->getTagName());
    }

    public function testOpacityIsOmittedByDefault(): void
    {
        $pattern = Pattern::dots();

        self::assertNull($pattern->opacity());
        self::assertSame('circle', $pattern->element()->getChildren()[0]->getTagName());
    }

    public function testOpacityIsCarriedByASingleGroup(): void
    {
        $element = Pattern::grid()->withOpacity(0.4)->element();
        $children = $element->getChildren();

        self::assertCount(1, $children);
        self::assertSame('g', $children[0]->getTagName());
        self::assertSame('0.4', $children[0]->getAttribute('opacity'));
    }

    public function testAngleIsCarriedByPatternTransform(): void
    {
        self::assertNull(Pattern::stripes()->element()->getPatternTransform());
        self::assertSame('rotate(30)', Pattern::stripes()->withAngle(30)->element()->getPatternTransform());
    }

    public function testStyleIsAppliedImmutably(): void
    {
        $plain = Pattern::dots();
        $styled = $plain->withColor('#c0392b')->withOpacity(0.4)->withAngle(30);

        self::assertNotSame($plain, $styled);
        self::assertSame('currentColor', $plain->color());
        self::assertNull($plain->opacity());
        self::assertSame(0.0, $plain->angle());

        self::assertSame('#c0392b', $styled->color());
        self::assertSame(0.4, $styled->opacity());
        self::assertSame(30.0, $styled->angle());
    }

    public function testColorReachesEveryShape(): void
    {
        $pattern = Pattern::grid()->withColor('#c0392b');

        foreach ($pattern->element()->getChildren() as $shape) {
            self::assertSame('#c0392b', $shape->getAttribute('fill'));
        }
    }

    public function testStrokeColorReachesOutlinedShapes(): void
    {
        $shapes = Shapes::of(Pattern::honeycomb()->withColor('#0f172a'));

        foreach ($shapes as $shape) {
            self::assertSame('none', $shape->getAttribute('fill'));
            self::assertSame('#0f172a', $shape->getAttribute('stroke'));
        }
    }

    public function testOpacityCanBeCleared(): void
    {
        $pattern = Pattern::dots()->withOpacity(0.4)->withOpacity(null);

        self::assertNull($pattern->opacity());
        self::assertSame('circle', $pattern->element()->getChildren()[0]->getTagName());
    }

    public function testIdentifierIsDerivedFromContent(): void
    {
        self::assertSame(Pattern::dots(spacing: 14, radius: 2.2)->id(), Pattern::dots(spacing: 14, radius: 2.2)->id());
        self::assertNotSame(Pattern::dots(spacing: 14)->id(), Pattern::dots(spacing: 15)->id());
    }

    public function testIdentifierNamesItsTile(): void
    {
        self::assertStringStartsWith('dots-', Pattern::dots()->id());
        self::assertStringStartsWith('isometric-cubes-', Pattern::isometricCubes()->id());
    }

    /**
     * @return iterable<string, array{AbstractPattern}>
     */
    public static function restyled(): iterable
    {
        yield 'color' => [Pattern::dots()->withColor('#c0392b')];
        yield 'opacity' => [Pattern::dots()->withOpacity(0.4)];
        yield 'angle' => [Pattern::dots()->withAngle(30)];
    }

    #[DataProvider('restyled')]
    public function testIdentifierFollowsStyle(AbstractPattern $styled): void
    {
        self::assertNotSame(Pattern::dots()->id(), $styled->id());
    }

    /**
     * @return iterable<string, array{string}>
     */
    public static function seededFactories(): iterable
    {
        foreach (['truchet', 'jitteredDots', 'roughHatch', 'staggeredBricks', 'confetti', 'voronoi', 'mosaic'] as $factory) {
            yield $factory => [$factory];
        }
    }

    #[DataProvider('seededFactories')]
    public function testLargeSeedsKeepDistinctIdentifiers(string $factory): void
    {
        $first = Pattern::$factory(seed: 9007199254740992);
        $second = Pattern::$factory(seed: 9007199254740993);
        $registry = new \Atelier\Pattern\PatternRegistry($first, $second);

        self::assertNotSame(serialize($first->withId('probe')->element()), serialize($second->withId('probe')->element()));
        self::assertNotSame($first->id(), $second->id());
        self::assertCount(2, $registry);
    }

    public function testIdentityPreservesGeometryBeforeCoordinateRounding(): void
    {
        $first = Pattern::dots(spacing: 10.00001, radius: 2.0);
        $second = Pattern::dots(spacing: 10.00004, radius: 2.0);

        self::assertNotSame($first->id(), $second->id());
    }

    public function testIdentityDoesNotDependOnPhpSerializationPrecision(): void
    {
        $pattern = Pattern::dots(spacing: 10.123456789);
        $expected = $pattern->id();
        $previous = ini_set('serialize_precision', '3');

        try {
            self::assertSame($expected, $pattern->id());
        } finally {
            ini_set('serialize_precision', $previous);
        }
    }

    public function testIdentifierUsesTheFullHash(): void
    {
        self::assertMatchesRegularExpression('/^dots-[a-f0-9]{32}$/', Pattern::dots()->id());
    }

    public function testIdentifierCanBeForced(): void
    {
        $pattern = Pattern::dots()->withId('page-dots');

        self::assertSame('page-dots', $pattern->id());
        self::assertSame('page-dots', $pattern->element()->getId());
        self::assertSame('url(#page-dots)', $pattern->fill());
    }

    public function testFillReferencesTheTile(): void
    {
        $pattern = Pattern::dots();

        self::assertSame('url(#'.$pattern->id().')', $pattern->fill());
    }

    public function testElementIsFreshOnEachCall(): void
    {
        $pattern = Pattern::dots();

        self::assertNotSame($pattern->element(), $pattern->element());
    }

    public function testBlankColorIsRejected(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('color must not be blank.');

        Pattern::dots()->withColor('  ');
    }

    public function testOpacityOutsideRangeIsRejected(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('opacity must be between 0 and 1, got 1.5.');

        Pattern::dots()->withOpacity(1.5);
    }

    public function testNonFiniteAngleIsRejected(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('angle must be a finite number, got INF.');

        Pattern::dots()->withAngle(\INF);
    }

    public function testBlankIdentifierIsRejected(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('id must not be blank.');

        Pattern::dots()->withId('');
    }
}
