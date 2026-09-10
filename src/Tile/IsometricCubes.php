<?php

declare(strict_types=1);

namespace Atelier\Pattern\Tile;

use Atelier\Pattern\AbstractPattern;
use Atelier\Pattern\Internal\Guard;
use Atelier\Pattern\Internal\Hex;
use Atelier\Pattern\Internal\Num;
use Atelier\Svg\Element\Shape\PolygonElement;

/**
 * Cubes stacked in isometric projection, out of the rhombille tiling.
 *
 * Each hexagon of the lattice is cut into three equal rhombi by the segments
 * running from its middle to the vertices at one, three and five o'clock. The
 * wide one sits on top, the two others stand upright on the vertical sides of
 * the hexagon: the outline of a cube seen from a corner. Three tones of the
 * same ink are enough for the volume to read.
 *
 * The tile is the one of the hexagonal lattice, sqrt(3)*size by 3*size, and
 * holds two hexagons: the one in the middle, and the four quarters of the
 * corner ones, which are translates of one another a period away.
 */
final class IsometricCubes extends AbstractPattern
{
    private function __construct(
        private readonly float $size,
        private readonly float $topFace,
        private readonly float $leftFace,
        private readonly float $rightFace,
    ) {
    }

    /**
     * @param float $size      edge of one cube, and radius of the hexagon it fills
     * @param float $topFace   ink on the face lying flat, the lightest of the three
     * @param float $leftFace  ink on the face turned to the left
     * @param float $rightFace ink on the face turned to the right
     */
    public static function create(float $size = 22.0, float $topFace = 0.3, float $leftFace = 0.85, float $rightFace = 0.55): self
    {
        Guard::positive($size, 'size');
        Guard::between($topFace, 0.0, 1.0, 'topFace');
        Guard::between($leftFace, 0.0, 1.0, 'leftFace');
        Guard::between($rightFace, 0.0, 1.0, 'rightFace');

        return new self($size, $topFace, $leftFace, $rightFace);
    }

    public function tileWidth(): float
    {
        return Hex::tileWidth($this->size);
    }

    public function tileHeight(): float
    {
        return Hex::tileHeight($this->size);
    }

    protected function shapes(): array
    {
        $shapes = [];

        foreach (Hex::centres($this->size) as [$cx, $cy]) {
            $vertices = Hex::vertices($cx, $cy, $this->size);

            // Each face runs from the middle out to three consecutive vertices.
            foreach ([[5, 0, 1, $this->topFace], [1, 2, 3, $this->rightFace], [3, 4, 5, $this->leftFace]] as [$first, $second, $third, $ink]) {
                $face = new PolygonElement();
                $face->setPoints($this->points([[$cx, $cy], $vertices[$first], $vertices[$second], $vertices[$third]]));
                $face->setAttribute('fill-opacity', Num::format($ink));

                $shapes[] = $this->painted($face);
            }
        }

        return $shapes;
    }

    protected function geometry(): array
    {
        return [$this->size, $this->topFace, $this->leftFace, $this->rightFace];
    }

    protected function slug(): string
    {
        return 'isometric-cubes';
    }

    /**
     * @param list<array{float, float}> $face
     */
    private function points(array $face): string
    {
        $points = [];

        foreach ($face as [$x, $y]) {
            $points[] = Num::format($x).','.Num::format($y);
        }

        return implode(' ', $points);
    }
}
