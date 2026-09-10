<?php

declare(strict_types=1);

namespace Atelier\Pattern\Tile;

use Atelier\Pattern\AbstractPattern;
use Atelier\Pattern\Internal\Guard;
use Atelier\Pattern\Internal\Num;
use Atelier\Pattern\Internal\Random;
use Atelier\Pattern\Internal\Wrap;
use Atelier\Svg\Element\Shape\PolygonElement;

/**
 * Small shapes thrown across the surface: one per cell, anywhere in it, each
 * with its own number of sides, its own size and its own bearing.
 *
 * The repeating tile is a square of cells by cells and the disorder stops at
 * its border. A shape landing on a border overhangs the tile, so it is drawn a
 * second time one period away against the opposite border, and a shape landing
 * on a corner is drawn on all four.
 */
final class Confetti extends AbstractPattern
{
    /** Smallest share of the size a shape can be dealt. */
    private const float MIN_SCALE = 0.55;

    private function __construct(
        private readonly float $spacing,
        private readonly float $size,
        private readonly int $cells,
        private readonly int $seed,
    ) {
    }

    /**
     * @param float $spacing side of the cell one shape lands in
     * @param float $size    distance from the middle of a shape to its farthest vertex, at most half the spacing
     * @param int   $cells   cells per side of the repeating tile, at least 2
     * @param int   $seed    same seed, same drawing
     */
    public static function create(float $spacing = 20.0, float $size = 4.0, int $cells = 5, int $seed = 1): self
    {
        Guard::positive($spacing, 'spacing');
        Guard::positive($size, 'size');
        Guard::atMost(2 * $size, $spacing, 'shape diameter', 'spacing');
        Guard::atLeast($cells, 2, 'cells');

        return new self($spacing, $size, $cells, $seed);
    }

    public function tileWidth(): float
    {
        return $this->cells * $this->spacing;
    }

    public function tileHeight(): float
    {
        return $this->cells * $this->spacing;
    }

    protected function shapes(): array
    {
        $random = new Random($this->seed);
        $width = $this->tileWidth();
        $height = $this->tileHeight();

        $shapes = [];

        for ($row = 0; $row < $this->cells; ++$row) {
            for ($column = 0; $column < $this->cells; ++$column) {
                $cx = ($column + $random->float()) * $this->spacing;
                $cy = ($row + $random->float()) * $this->spacing;
                $sides = $random->int(3, 5);
                $radius = $random->between(self::MIN_SCALE * $this->size, $this->size);
                $bearing = $random->between(0.0, 2 * M_PI);

                $vertices = [[$cx + cos($bearing) * $radius, $cy + sin($bearing) * $radius]];

                for ($vertex = 1; $vertex < $sides; ++$vertex) {
                    $direction = $bearing + $vertex * 2 * M_PI / $sides;
                    $vertices[] = [$cx + cos($direction) * $radius, $cy + sin($direction) * $radius];
                }

                $offsets = Wrap::offsets(
                    min(array_column($vertices, 0)),
                    min(array_column($vertices, 1)),
                    max(array_column($vertices, 0)),
                    max(array_column($vertices, 1)),
                    $width,
                    $height,
                );

                foreach ($offsets as [$dx, $dy]) {
                    $shape = new PolygonElement();
                    $shape->setPoints($this->points($vertices, $dx, $dy));

                    $shapes[] = $this->painted($shape);
                }
            }
        }

        return $shapes;
    }

    protected function geometry(): array
    {
        return [$this->spacing, $this->size, $this->cells, $this->seed];
    }

    protected function slug(): string
    {
        return 'confetti';
    }

    /**
     * @param list<array{float, float}> $vertices
     */
    private function points(array $vertices, float $dx, float $dy): string
    {
        $points = [];

        foreach ($vertices as [$x, $y]) {
            $points[] = Num::format($x + $dx).','.Num::format($y + $dy);
        }

        return implode(' ', $points);
    }
}
