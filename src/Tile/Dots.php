<?php

declare(strict_types=1);

namespace Atelier\Pattern\Tile;

use Atelier\Pattern\AbstractPattern;
use Atelier\Pattern\Internal\Guard;
use Atelier\Pattern\Internal\Num;
use Atelier\Pattern\Internal\Wrap;
use Atelier\Svg\Element\Shape\CircleElement;

/**
 * A lattice of dots, one per cell.
 *
 * Two fractions of the spacing settle the lattice. `stagger` shifts every other
 * row, which spans the tile over two rows: 0 leaves the square lattice, 0.5
 * gives the staggered one that hides the columns. `phase` slides the whole
 * lattice along both axes: 0 keeps the dot at the middle of its cell, 0.5 puts
 * it on the node of the grid the cells draw.
 *
 * A dot pushed onto an edge is drawn again a period away, once per edge its
 * envelope crosses, so the halves rebuild one dot across the seam.
 */
final class Dots extends AbstractPattern
{
    private function __construct(
        private readonly float $spacing,
        private readonly float $radius,
        private readonly float $stagger,
        private readonly float $phase,
    ) {
    }

    /**
     * @param float $spacing distance between two dots of the same row
     * @param float $radius  dot radius, at most half the spacing
     * @param float $stagger shift of every other row, as a fraction of the spacing
     * @param float $phase   shift of the whole lattice on both axes, as a fraction of the spacing
     */
    public static function create(float $spacing = 14.0, float $radius = 2.2, float $stagger = 0.0, float $phase = 0.0): self
    {
        Guard::positive($spacing, 'spacing');
        Guard::positive($radius, 'radius');
        Guard::atMost(2 * $radius, $spacing, 'dot diameter', 'spacing');
        Guard::fraction($stagger, 'stagger');
        Guard::fraction($phase, 'phase');

        return new self($spacing, $radius, $stagger, $phase);
    }

    public function tileWidth(): float
    {
        return $this->spacing;
    }

    public function tileHeight(): float
    {
        // A staggered lattice only comes back to itself after two rows.
        return 0.0 === $this->stagger ? $this->spacing : 2 * $this->spacing;
    }

    protected function shapes(): array
    {
        $height = $this->tileHeight();
        $shapes = [];

        foreach ($this->centres() as [$cx, $cy]) {
            $offsets = Wrap::offsets($cx - $this->radius, $cy - $this->radius, $cx + $this->radius, $cy + $this->radius, $this->spacing, $height);

            foreach ($offsets as [$dx, $dy]) {
                $dot = new CircleElement();
                $dot->setCx(Num::format($cx + $dx));
                $dot->setCy(Num::format($cy + $dy));
                $dot->setR(Num::format($this->radius));

                $shapes[] = $this->painted($dot);
            }
        }

        return $shapes;
    }

    protected function geometry(): array
    {
        return [$this->spacing, $this->radius, $this->stagger, $this->phase];
    }

    protected function slug(): string
    {
        return 'dots';
    }

    /**
     * One dot per row of the tile, brought back inside it.
     *
     * A centre is taken modulo the tile so a lattice slid by a phase keeps its
     * dots on the tile it belongs to, and the wrapping stays a matter of the
     * radius alone.
     *
     * @return list<array{float, float}>
     */
    private function centres(): array
    {
        $height = $this->tileHeight();
        $slide = $this->phase * $this->spacing;
        $rows = 0.0 === $this->stagger ? 1 : 2;

        $centres = [];

        for ($row = 0; $row < $rows; ++$row) {
            $centres[] = [
                fmod(($row * $this->stagger + 0.5) * $this->spacing + $slide, $this->spacing),
                fmod(($row + 0.5) * $this->spacing + $slide, $height),
            ];
        }

        return $centres;
    }
}
