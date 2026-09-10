<?php

declare(strict_types=1);

namespace Atelier\Pattern\Tile;

use Atelier\Pattern\AbstractPattern;
use Atelier\Pattern\Internal\Guard;
use Atelier\Pattern\Internal\Num;
use Atelier\Pattern\Internal\Wrap;
use Atelier\Svg\Element\Shape\CircleElement;

/**
 * The quatrefoil of a moroccan screen: four lobes to a rosette, on a lattice
 * that carries a second rosette in the middle of every cell.
 *
 * The rosette is four discs on the arms of a cross, close enough to merge into
 * one shape. Filling them with one ink is what unions them: no boolean is run,
 * and the outline nobody draws is the one the eye reads.
 *
 * What the rosettes leave between them is the subject as much as the rosettes:
 * the gap closes into the pointed ogee the tiling is named for. Lobes larger
 * than a quarter of the lattice flood that gap and the surface goes solid, so
 * the guard stops there.
 */
final class Quatrefoil extends AbstractPattern
{
    /** How far a lobe sits from the middle of its rosette, as a share of its own radius. */
    private const float REACH = 0.92;

    private function __construct(
        private readonly float $size,
        private readonly float $lobe,
    ) {
    }

    /**
     * @param float $size side of the lattice cell
     * @param float $lobe radius of one lobe, at most a quarter of the cell
     */
    public static function create(float $size = 34.0, float $lobe = 6.4): self
    {
        Guard::positive($size, 'size');
        Guard::positive($lobe, 'lobe');
        Guard::atMost($lobe, $size / 4, 'lobe', 'a quarter of the size');

        return new self($size, $lobe);
    }

    public function tileWidth(): float
    {
        return $this->size;
    }

    public function tileHeight(): float
    {
        return $this->size;
    }

    protected function shapes(): array
    {
        $reach = $this->lobe * self::REACH;
        $shapes = [];

        foreach ([[0.0, 0.0], [$this->size / 2, $this->size / 2]] as [$cx, $cy]) {
            foreach ([[-$reach, 0.0], [$reach, 0.0], [0.0, -$reach], [0.0, $reach]] as [$ax, $ay]) {
                $x = $cx + $ax;
                $y = $cy + $ay;

                $offsets = Wrap::offsets(
                    $x - $this->lobe,
                    $y - $this->lobe,
                    $x + $this->lobe,
                    $y + $this->lobe,
                    $this->tileWidth(),
                    $this->tileHeight(),
                );

                foreach ($offsets as [$dx, $dy]) {
                    $disc = new CircleElement();
                    $disc->setCx(Num::format($x + $dx));
                    $disc->setCy(Num::format($y + $dy));
                    $disc->setR(Num::format($this->lobe));

                    $shapes[] = $this->painted($disc);
                }
            }
        }

        return $shapes;
    }

    protected function geometry(): array
    {
        return [$this->size, $this->lobe];
    }

    protected function slug(): string
    {
        return 'quatrefoil';
    }
}
