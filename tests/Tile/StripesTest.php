<?php

declare(strict_types=1);

namespace Atelier\Pattern\Tests\Tile;

use Atelier\Pattern\Exception\InvalidArgumentException;
use Atelier\Pattern\Tests\Support\Shapes;
use Atelier\Pattern\Tests\Support\TilingAssertions;
use Atelier\Pattern\Tile\Stripes;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(Stripes::class)]
final class StripesTest extends TestCase
{
    use TilingAssertions;

    public function testTileIsOneSpacingSquare(): void
    {
        $stripes = Stripes::create(spacing: 12, thickness: 4);

        self::assertSame(12.0, $stripes->tileWidth());
        self::assertSame(12.0, $stripes->tileHeight());
    }

    public function testTheBandRunsTheFullHeightAndStaysInsideTheTile(): void
    {
        $stripes = Stripes::create(spacing: 12, thickness: 4);

        self::assertSame([['x' => 0.0, 'y' => 0.0, 'width' => 4.0, 'height' => 12.0]], Shapes::rects($stripes));
        self::assertCirclesAndRectsJoin($stripes);
    }

    public function testABandAsWideAsTheSpacingFillsTheTile(): void
    {
        $stripes = Stripes::create(spacing: 12, thickness: 12);

        self::assertSame([['x' => 0.0, 'y' => 0.0, 'width' => 12.0, 'height' => 12.0]], Shapes::rects($stripes));
    }

    public function testTheAngleTurnsTheTilingThroughPatternTransform(): void
    {
        $stripes = Stripes::create(spacing: 12, thickness: 4, angle: 45);

        self::assertSame(45.0, $stripes->angle());
        self::assertSame('rotate(45)', $stripes->element()->getPatternTransform());
        self::assertSame([['x' => 0.0, 'y' => 0.0, 'width' => 4.0, 'height' => 12.0]], Shapes::rects($stripes));
    }

    public function testTheAngleArgumentAndWithAngleGiveTheSameTile(): void
    {
        self::assertSame(
            Stripes::create(spacing: 12, thickness: 4)->withAngle(45)->id(),
            Stripes::create(spacing: 12, thickness: 4, angle: 45)->id(),
        );
    }

    public function testAnUnturnedTileCarriesNoTransform(): void
    {
        self::assertSame(Stripes::create(spacing: 12, thickness: 4)->id(), Stripes::create(spacing: 12, thickness: 4, angle: 0)->id());
        self::assertNull(Stripes::create(spacing: 12, thickness: 4, angle: 0)->element()->getPatternTransform());
    }

    public function testTheAngleMustBeAFiniteNumber(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('angle must be a finite number, got INF.');

        Stripes::create(spacing: 12, thickness: 4, angle: \INF);
    }

    public function testABandWiderThanTheSpacingIsRejected(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('thickness must not exceed spacing (12), got 13.');

        Stripes::create(spacing: 12, thickness: 13);
    }

    public function testSpacingMustBePositive(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('spacing must be a finite number greater than 0');

        Stripes::create(spacing: 0);
    }

    public function testThicknessMustBePositive(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('thickness must be a finite number greater than 0');

        Stripes::create(spacing: 12, thickness: 0);
    }
}
