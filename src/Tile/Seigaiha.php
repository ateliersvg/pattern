<?php

declare(strict_types=1);

namespace Atelier\Pattern\Tile;

use Atelier\Pattern\AbstractPattern;
use Atelier\Pattern\Internal\Guard;
use Atelier\Pattern\Internal\Num;
use Atelier\Pattern\Internal\Wrap;
use Atelier\Svg\Element\PathElement;

/**
 * Seigaiha, the blue ocean wave: fans of concentric arcs, each row standing
 * half a fan across from the one behind it and hiding most of it.
 *
 * The hiding is the pattern. On paper the fan in front is opaque and covers
 * what is behind it, and a tile paints no ground, so there is nothing here to
 * cover with. Each arc is cut instead: the span it spends inside a fan in front
 * is read off the two circle intersections and dropped, and what is left is the
 * scallop.
 *
 * Fans touch rather than overlap along a row, so only the rows in front do any
 * cutting. The rise decides how much of each fan survives, and at a rise of a
 * radius the rows separate and the surface reads as a row of suns.
 */
final class Seigaiha extends AbstractPattern
{
    /** How far a row is offset from the one behind it, as a share of the tile width. */
    private const float OFFSET = 0.5;

    private function __construct(
        private readonly float $radius,
        private readonly float $rise,
        private readonly int $rings,
        private readonly float $thickness,
    ) {
    }

    /**
     * @param float $radius    radius of the outer arc of a fan
     * @param float $rise      distance between two rows, at most half the radius
     * @param int   $rings     arcs to a fan, 2 to 8
     * @param float $thickness line width, at most a tenth of the radius
     */
    public static function create(float $radius = 26.0, float $rise = 12.0, int $rings = 4, float $thickness = 1.1): self
    {
        Guard::positive($radius, 'radius');
        Guard::positive($rise, 'rise');
        Guard::atMost($rise, $radius / 2, 'rise', 'half the radius');
        Guard::atLeast($rings, 2, 'rings');
        Guard::atMost((float) $rings, 8.0, 'rings', '8');
        Guard::positive($thickness, 'thickness');
        Guard::atMost($thickness, $radius / 10, 'thickness', 'a tenth of the radius');

        return new self($radius, $rise, $rings, $thickness);
    }

    public function tileWidth(): float
    {
        return 2 * $this->radius;
    }

    public function tileHeight(): float
    {
        return 2 * $this->rise;
    }

    protected function shapes(): array
    {
        $width = $this->tileWidth();
        $height = $this->tileHeight();
        $shapes = [];

        // One fan per row of the tile. Every other fan in the plane is one of
        // these two, a whole number of periods away.
        foreach ([[0.0, 0.0], [$this->radius, $this->rise]] as [$cx, $cy]) {
            foreach ($this->radii() as $radius) {
                foreach ($this->spans($cx, $cy, $radius) as [$from, $to]) {
                    [$minX, $minY, $maxX, $maxY] = $this->envelope($cx, $cy, $radius, $from, $to);

                    foreach (Wrap::offsets($minX, $minY, $maxX, $maxY, $width, $height) as [$dx, $dy]) {
                        $arc = new PathElement();
                        $arc->setPathData($this->arc($cx + $dx, $cy + $dy, $radius, $from, $to));

                        $shapes[] = $this->outlined($arc, $this->thickness);
                    }
                }
            }
        }

        return $shapes;
    }

    protected function geometry(): array
    {
        return [$this->radius, $this->rise, $this->rings, $this->thickness];
    }

    protected function slug(): string
    {
        return 'seigaiha';
    }

    /**
     * The arcs of one fan, from the outer one inwards.
     *
     * @return list<float>
     */
    private function radii(): array
    {
        $radii = [];

        for ($ring = 0; $ring < $this->rings; ++$ring) {
            $radii[] = $this->radius * (1.0 - $ring / $this->rings);
        }

        return $radii;
    }

    /**
     * What is left of the upper half of a circle once the fans in front of it
     * have taken their share.
     *
     * @return list<array{float, float}> angular spans, in radians
     */
    private function spans(float $cx, float $cy, float $radius): array
    {
        $spans = [[M_PI, 2 * M_PI]];

        foreach ($this->blockers($cx, $cy) as [$bx, $by]) {
            $distance = hypot($bx - $cx, $by - $cy);

            if ($distance <= 0.0 || $distance >= $radius + $this->radius) {
                continue;
            }

            if ($distance + $radius <= $this->radius) {
                return [];
            }

            $cosine = ($distance ** 2 + $radius ** 2 - $this->radius ** 2) / (2 * $distance * $radius);

            if ($cosine >= 1.0 || $cosine <= -1.0) {
                continue;
            }

            $half = acos($cosine);
            $bearing = atan2($by - $cy, $bx - $cx);

            // The blocked span is read on three turns, because a span written
            // between pi and two pi can be met by one written around zero.
            foreach ([-2 * M_PI, 0.0, 2 * M_PI] as $turn) {
                $spans = $this->without($spans, $bearing - $half + $turn, $bearing + $half + $turn);
            }
        }

        return array_values(array_filter($spans, fn (array $span): bool => $span[1] - $span[0] > 0.01));
    }

    /**
     * The fans standing in front of one, near enough to cut into it.
     *
     * @return list<array{float, float}>
     */
    private function blockers(float $cx, float $cy): array
    {
        $blockers = [];
        $rows = (int) ceil($this->radius / $this->rise);

        for ($row = 1; $row <= $rows; ++$row) {
            $shift = 0 === $row % 2 ? 0.0 : self::OFFSET * $this->tileWidth();

            for ($column = -1; $column <= 1; ++$column) {
                $blockers[] = [$cx + $shift + $column * $this->tileWidth(), $cy + $row * $this->rise];
            }
        }

        return $blockers;
    }

    /**
     * @param list<array{float, float}> $spans
     *
     * @return list<array{float, float}>
     */
    private function without(array $spans, float $from, float $to): array
    {
        $kept = [];

        foreach ($spans as [$start, $end]) {
            if ($to <= $start || $from >= $end) {
                $kept[] = [$start, $end];

                continue;
            }

            if ($from > $start) {
                $kept[] = [$start, min($from, $end)];
            }

            if ($to < $end) {
                $kept[] = [max($to, $start), $end];
            }
        }

        return $kept;
    }

    /**
     * @return array{float, float, float, float}
     */
    private function envelope(float $cx, float $cy, float $radius, float $from, float $to): array
    {
        $xs = [$cx + $radius * cos($from), $cx + $radius * cos($to)];
        $ys = [$cy + $radius * sin($from), $cy + $radius * sin($to)];

        // An extreme of the circle counts only when the span reaches it.
        foreach ([M_PI, 1.5 * M_PI, 2 * M_PI] as $angle) {
            if ($angle >= $from && $angle <= $to) {
                $xs[] = $cx + $radius * cos($angle);
                $ys[] = $cy + $radius * sin($angle);
            }
        }

        return [min($xs), min($ys), max($xs), max($ys)];
    }

    private function arc(float $cx, float $cy, float $radius, float $from, float $to): string
    {
        $sweep = $to - $from > M_PI ? 1 : 0;

        return 'M '.Num::format($cx + $radius * cos($from)).' '.Num::format($cy + $radius * sin($from))
            .' A '.Num::format($radius).' '.Num::format($radius).' 0 '.$sweep.' 1 '
            .Num::format($cx + $radius * cos($to)).' '.Num::format($cy + $radius * sin($to));
    }
}
