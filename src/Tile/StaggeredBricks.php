<?php

declare(strict_types=1);

namespace Atelier\Pattern\Tile;

use Atelier\Pattern\AbstractPattern;
use Atelier\Pattern\Internal\Guard;
use Atelier\Pattern\Internal\Num;
use Atelier\Pattern\Internal\Random;
use Atelier\Pattern\Internal\Wrap;
use Atelier\Svg\Element\Shape\RectElement;

/**
 * Brickwork whose bond is drawn at random rather than measured.
 *
 * A course is one bed joint running the width of the tile and one head joint
 * standing between two bricks. A running bond puts the head joint exactly half
 * a brick off the course below; here it lands within jitter of that mark, so no
 * two courses read the same.
 *
 * The tile is one brick wide and cells courses tall, and the bond has to come
 * back where it started after those courses or the seam between two tiles shows
 * a straight crack. It steps by intdiv(cells, 2) bricks over cells courses,
 * which closes for any count and is exactly half a brick whenever the count is
 * even.
 *
 * The bed joint spans the tile edge to edge; a head joint straddling the
 * vertical edge is drawn a second time one period away, against the opposite
 * edge.
 */
final class StaggeredBricks extends AbstractPattern
{
    private function __construct(
        private readonly float $width,
        private readonly float $height,
        private readonly float $thickness,
        private readonly float $jitter,
        private readonly int $cells,
        private readonly int $seed,
    ) {
    }

    /**
     * @param float $width     length of one brick
     * @param float $height    height of one course
     * @param float $thickness joint width, at most the brick length and at most the course height
     * @param float $jitter    how far a course leaves the bond mark, as a share of the brick
     * @param int   $cells     courses of the repeating tile, at least 2
     * @param int   $seed      same seed, same drawing
     */
    public static function create(float $width = 28.0, float $height = 12.0, float $thickness = 1.2, float $jitter = 0.2, int $cells = 4, int $seed = 1): self
    {
        Guard::positive($width, 'width');
        Guard::positive($height, 'height');
        Guard::positive($thickness, 'thickness');
        Guard::atMost($thickness, $width, 'thickness', 'width');
        Guard::atMost($thickness, $height, 'thickness', 'height');
        Guard::atLeast($cells, 2, 'cells');
        Guard::between($jitter, 0.0, self::jitterLimit($width, $thickness, $cells), 'jitter');

        return new self($width, $height, $thickness, $jitter, $cells, $seed);
    }

    public function tileWidth(): float
    {
        return $this->width;
    }

    public function tileHeight(): float
    {
        return $this->cells * $this->height;
    }

    protected function shapes(): array
    {
        $random = new Random($this->seed);
        $height = $this->tileHeight();

        $shapes = [];

        for ($course = 0; $course < $this->cells; ++$course) {
            $top = $course * $this->height;

            $bed = new RectElement();
            $bed->setX('0');
            $bed->setY(Num::format($top));
            $bed->setWidth(Num::format($this->width));
            $bed->setHeight(Num::format($this->thickness));

            $shapes[] = $this->painted($bed);

            $offset = $this->headJoint($course, $random);

            $offsets = Wrap::offsets(
                $offset,
                $top,
                $offset + $this->thickness,
                $top + $this->height,
                $this->width,
                $height,
            );

            foreach ($offsets as [$dx, $dy]) {
                $joint = new RectElement();
                $joint->setX(Num::format($offset + $dx));
                $joint->setY(Num::format($top + $dy));
                $joint->setWidth(Num::format($this->thickness));
                $joint->setHeight(Num::format($this->height));

                $shapes[] = $this->painted($joint);
            }
        }

        return $shapes;
    }

    protected function geometry(): array
    {
        return [$this->width, $this->height, $this->thickness, $this->jitter, $this->cells, $this->seed];
    }

    protected function slug(): string
    {
        return 'staggered-bricks';
    }

    /**
     * Where the head joint of a course stands, in [0, width): the bond mark for
     * that course, pulled off it, folded back into the tile.
     */
    private function headJoint(int $course, Random $random): float
    {
        $bond = $course * $this->bondStep() + $random->between(-$this->jitter, $this->jitter);

        return fmod(($bond + 1.0) * $this->width, $this->width);
    }

    /**
     * How far the bond moves per course, in bricks. Half a brick is the mason's
     * answer, but it only comes back to its start after an even number of
     * courses; this is the nearest step that closes after the count asked for.
     */
    private function bondStep(): float
    {
        return self::stepFor($this->cells);
    }

    private static function stepFor(int $cells): float
    {
        return intdiv($cells, 2) / $cells;
    }

    /**
     * The most a course may leave the bond mark before two head joints touch.
     *
     * Two courses each wobble by up to the jitter, so their offsets move apart
     * by up to twice it. Past that the bond step is used up and the joint lands
     * on the one below, which is the straight crack a bond exists to avoid.
     */
    private static function jitterLimit(float $width, float $thickness, int $cells): float
    {
        $step = self::stepFor($cells);
        $joint = $thickness / $width;

        return max(0.0, min($step - $joint, 1 - $step - $joint) / 2);
    }
}
