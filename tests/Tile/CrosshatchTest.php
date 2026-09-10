<?php

declare(strict_types=1);

namespace Atelier\Pattern\Tests\Tile;

use Atelier\Pattern\Exception\InvalidArgumentException;
use Atelier\Pattern\Tests\Support\Shapes;
use Atelier\Pattern\Tile\Crosshatch;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(Crosshatch::class)]
final class CrosshatchTest extends TestCase
{
    public function testTileIsOneSpacingSquare(): void
    {
        $hatch = Crosshatch::create(spacing: 14, thickness: 1.2);

        self::assertSame(14.0, $hatch->tileWidth());
        self::assertSame(14.0, $hatch->tileHeight());
    }

    public function testBothDiagonalsRunCornerToCorner(): void
    {
        $subpaths = Shapes::subpaths(Crosshatch::create(spacing: 14, thickness: 1.2));

        self::assertSame(['M 0 0 L 14 14', 'M 14 0 L 0 14'], $subpaths);
    }

    public function testTheCornersOfTheTileAreTheOnlyEndpoints(): void
    {
        $hatch = Crosshatch::create(spacing: 14, thickness: 1.2);

        foreach (Shapes::subpaths($hatch) as $subpath) {
            foreach (Shapes::numbersIn($subpath) as $coordinate) {
                self::assertContains($coordinate, [0.0, $hatch->tileWidth()]);
            }
        }
    }

    public function testTheLineIsStrokedAtTheGivenThickness(): void
    {
        $shapes = Shapes::of(Crosshatch::create(spacing: 14, thickness: 1.2));

        self::assertSame('1.2', $shapes[0]->getAttribute('stroke-width'));
        self::assertSame('none', $shapes[0]->getAttribute('fill'));
    }

    public function testTheAngleTurnsTheTilingThroughPatternTransform(): void
    {
        $hatch = Crosshatch::create(spacing: 14, thickness: 1.2, angle: 30);

        self::assertSame(30.0, $hatch->angle());
        self::assertSame('rotate(30)', $hatch->element()->getPatternTransform());
        self::assertSame(['M 0 0 L 14 14', 'M 14 0 L 0 14'], Shapes::subpaths($hatch));
    }

    public function testTheAngleArgumentAndWithAngleGiveTheSameTile(): void
    {
        self::assertSame(
            Crosshatch::create(spacing: 14, thickness: 1.2)->withAngle(30)->id(),
            Crosshatch::create(spacing: 14, thickness: 1.2, angle: 30)->id(),
        );
    }

    public function testAnUnturnedTileCarriesNoTransform(): void
    {
        self::assertSame(Crosshatch::create(spacing: 14, thickness: 1.2)->id(), Crosshatch::create(spacing: 14, thickness: 1.2, angle: 0)->id());
        self::assertNull(Crosshatch::create(spacing: 14, thickness: 1.2, angle: 0)->element()->getPatternTransform());
    }

    public function testTheAngleMustBeAFiniteNumber(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('angle must be a finite number, got NAN.');

        Crosshatch::create(spacing: 14, thickness: 1.2, angle: \NAN);
    }

    public function testALineThickerThanTheSpacingIsRejected(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('thickness must not exceed spacing (14), got 15.');

        Crosshatch::create(spacing: 14, thickness: 15);
    }

    public function testSpacingMustBePositive(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('spacing must be a finite number greater than 0');

        Crosshatch::create(spacing: 0);
    }

    public function testThicknessMustBePositive(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('thickness must be a finite number greater than 0');

        Crosshatch::create(spacing: 14, thickness: -1);
    }
}
