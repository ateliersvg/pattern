<?php

declare(strict_types=1);

namespace Atelier\Pattern\Tile;

use Atelier\Pattern\AbstractPattern;
use Atelier\Pattern\Internal\Guard;
use Atelier\Pattern\Internal\Num;
use Atelier\Pattern\Internal\Random;
use Atelier\Pattern\Internal\Wrap;
use Atelier\Svg\Element\Shape\CircleElement;

/**
 * A lattice of dots, each pushed off its node by an amount drawn at random.
 *
 * The two axes are drawn apart, each within jitter times the spacing, so a dot
 * lands anywhere in the square its jitter allows rather than piling up near its
 * node. At a jitter of 0.5 that square is the cell itself and the lattice stops
 * showing through; below it the rows still read, above it the dots of two cells
 * mix. The repeating tile is a square of cells by cells, and the disorder stops
 * at its border.
 *
 * The nodes sit on the corners of the cells, so the first row and the first
 * column land on the edges of the tile. A dot pushed off one of those overhangs
 * the tile: it is drawn a second time one period away against the opposite
 * edge, and a dot on the corner node is drawn on all four corners. The clip
 * then takes nothing away, and the pieces rebuild one dot across the seam.
 */
final class JitteredDots extends AbstractPattern
{
    /** Beyond this a dot reaches past a neighbouring node and the lattice is gone. */
    private const float MAX_JITTER = 1.0;

    private function __construct(
        private readonly float $spacing,
        private readonly float $radius,
        private readonly float $jitter,
        private readonly int $cells,
        private readonly int $seed,
    ) {
    }

    /**
     * @param float $spacing distance between two lattice nodes
     * @param float $radius  dot radius, at most half the spacing
     * @param float $jitter  how far a dot leaves its node on each axis, as a share of the spacing, at most 1
     * @param int   $cells   cells per side of the repeating tile, at least 2
     * @param int   $seed    same seed, same drawing
     */
    public static function create(float $spacing = 16.0, float $radius = 2.4, float $jitter = 0.5, int $cells = 6, int $seed = 1): self
    {
        Guard::positive($spacing, 'spacing');
        Guard::positive($radius, 'radius');
        Guard::atMost(2 * $radius, $spacing, 'dot diameter', 'spacing');
        Guard::between($jitter, 0.0, self::MAX_JITTER, 'jitter');
        Guard::atLeast($cells, 2, 'cells');

        return new self($spacing, $radius, $jitter, $cells, $seed);
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
        $reach = $this->jitter * $this->spacing;

        $shapes = [];

        for ($row = 0; $row < $this->cells; ++$row) {
            for ($column = 0; $column < $this->cells; ++$column) {
                $cx = $column * $this->spacing + $random->between(-$reach, $reach);
                $cy = $row * $this->spacing + $random->between(-$reach, $reach);

                $offsets = Wrap::offsets(
                    $cx - $this->radius,
                    $cy - $this->radius,
                    $cx + $this->radius,
                    $cy + $this->radius,
                    $width,
                    $height,
                );

                foreach ($offsets as [$dx, $dy]) {
                    $dot = new CircleElement();
                    $dot->setCx(Num::format($cx + $dx));
                    $dot->setCy(Num::format($cy + $dy));
                    $dot->setR(Num::format($this->radius));

                    $shapes[] = $this->painted($dot);
                }
            }
        }

        return $shapes;
    }

    protected function geometry(): array
    {
        return [$this->spacing, $this->radius, $this->jitter, $this->cells, $this->seed];
    }

    protected function slug(): string
    {
        return 'jittered-dots';
    }
}
