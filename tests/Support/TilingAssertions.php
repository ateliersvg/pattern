<?php

declare(strict_types=1);

namespace Atelier\Pattern\Tests\Support;

use Atelier\Pattern\AbstractPattern;
use PHPUnit\Framework\Assert;

/**
 * The joining rule, stated for the shapes whose envelope is computable.
 *
 * Anything leaving through one edge must come back through the opposite one:
 * for every circle, rectangle or point list crossing an edge, the same shape
 * translated by one period has to be on the other side.
 */
trait TilingAssertions
{
    private const float TOLERANCE = 0.0001;

    protected static function assertCirclesAndRectsJoin(AbstractPattern $pattern): void
    {
        $width = $pattern->tileWidth();
        $height = $pattern->tileHeight();

        $circles = array_map(
            static fn (array $circle): array => [$circle['cx'], $circle['cy'], $circle['r']],
            Shapes::circles($pattern),
        );

        $rects = array_map(
            static fn (array $rect): array => [$rect['x'], $rect['y'], $rect['width'], $rect['height']],
            Shapes::rects($pattern),
        );

        $missing = [];

        foreach ($circles as [$cx, $cy, $r]) {
            $missing = [
                ...$missing,
                ...self::missingTranslates([$cx - $r, $cy - $r, $cx + $r, $cy + $r], $circles, [$cx, $cy, $r], $width, $height, 'circle'),
            ];
        }

        foreach ($rects as [$x, $y, $w, $h]) {
            $missing = [
                ...$missing,
                ...self::missingTranslates([$x, $y, $x + $w, $y + $h], $rects, [$x, $y, $w, $h], $width, $height, 'rect'),
            ];
        }

        Assert::assertSame([], $missing, 'A shape crosses an edge without its translate on the opposite edge.');
    }

    protected static function assertPolygonsJoin(AbstractPattern $pattern): void
    {
        self::assertPointListsJoin(Shapes::polygons($pattern), $pattern->tileWidth(), $pattern->tileHeight(), 'polygon');
    }

    /**
     * The same rule for anything drawn as a run of points: a polygon, or the
     * nodes of a polyline.
     *
     * The margins widen the envelope by what the paint adds around the points,
     * half a stroke width. They are given per axis: a stroke that ends on an
     * edge on purpose puts its cap on the very same point as the cap of the
     * tile next door, so there is nothing to translate along that axis.
     *
     * @param list<list<array{0: float, 1: float}>> $lists
     */
    protected static function assertPointListsJoin(array $lists, float $width, float $height, string $kind, float $marginX = 0.0, float $marginY = 0.0): void
    {
        $missing = [];

        foreach ($lists as $points) {
            $box = self::envelopeOf($points, $marginX, $marginY);

            foreach (self::shiftsFor($box, $width, $height) as [$dx, $dy]) {
                $wanted = array_map(
                    static fn (array $point): array => [$point[0] + $dx, $point[1] + $dy],
                    $points,
                );

                if (!self::containsPoints($lists, $wanted)) {
                    $missing[] = \sprintf('%s at (%s, %s) translated by (%s, %s)', $kind, $points[0][0], $points[0][1], $dx, $dy);
                }
            }
        }

        Assert::assertSame([], $missing, 'A shape crosses an edge without its translate on the opposite edge.');
    }

    /**
     * How many edge crossings the rule was checked against, so a test can prove
     * it asserted something.
     *
     * @param list<list<array{0: float, 1: float}>> $lists
     */
    protected static function countEdgeCrossings(array $lists, float $width, float $height, float $marginX = 0.0, float $marginY = 0.0): int
    {
        $crossings = 0;

        foreach ($lists as $points) {
            $crossings += \count(self::shiftsFor(self::envelopeOf($points, $marginX, $marginY), $width, $height));
        }

        return $crossings;
    }

    /**
     * @param list<array{0: float, 1: float}> $points
     *
     * @return array{0: float, 1: float, 2: float, 3: float}
     */
    private static function envelopeOf(array $points, float $marginX, float $marginY): array
    {
        $xs = array_column($points, 0);
        $ys = array_column($points, 1);

        return [min($xs) - $marginX, min($ys) - $marginY, max($xs) + $marginX, max($ys) + $marginY];
    }

    /**
     * @param array{0: float, 1: float, 2: float, 3: float} $box       envelope as minX, minY, maxX, maxY
     * @param list<list<float>>                             $siblings  every shape of the tile, in the same encoding
     * @param list<float>                                   $signature the shape itself, position first
     *
     * @return list<string>
     */
    private static function missingTranslates(array $box, array $siblings, array $signature, float $width, float $height, string $kind): array
    {
        $missing = [];

        foreach (self::shiftsFor($box, $width, $height) as [$dx, $dy]) {
            $wanted = $signature;
            $wanted[0] += $dx;
            $wanted[1] += $dy;

            if (!self::contains($siblings, $wanted)) {
                $missing[] = \sprintf('%s at (%s, %s) translated by (%s, %s)', $kind, $signature[0], $signature[1], $dx, $dy);
            }
        }

        return $missing;
    }

    /**
     * Where a shape with this envelope has to be repeated for the tile to join.
     *
     * @param array{0: float, 1: float, 2: float, 3: float} $box envelope as minX, minY, maxX, maxY
     *
     * @return list<array{0: float, 1: float}>
     */
    private static function shiftsFor(array $box, float $width, float $height): array
    {
        [$minX, $minY, $maxX, $maxY] = $box;

        $shifts = [];

        if ($minX < -self::TOLERANCE) {
            $shifts[] = [$width, 0.0];
        }

        if ($maxX > $width + self::TOLERANCE) {
            $shifts[] = [-$width, 0.0];
        }

        if ($minY < -self::TOLERANCE) {
            $shifts[] = [0.0, $height];
        }

        if ($maxY > $height + self::TOLERANCE) {
            $shifts[] = [0.0, -$height];
        }

        if (\count($shifts) > 1) {
            $shifts[] = [$shifts[0][0] + $shifts[1][0], $shifts[0][1] + $shifts[1][1]];
        }

        return $shifts;
    }

    /**
     * @param list<list<float>> $haystack
     * @param list<float>       $needle
     */
    private static function contains(array $haystack, array $needle): bool
    {
        foreach ($haystack as $candidate) {
            if (\count($candidate) !== \count($needle)) {
                continue;
            }

            $same = true;

            foreach ($needle as $index => $value) {
                if (abs($candidate[$index] - $value) > self::TOLERANCE) {
                    $same = false;

                    break;
                }
            }

            if ($same) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param list<list<array{0: float, 1: float}>> $haystack
     * @param list<array{0: float, 1: float}>       $needle
     */
    private static function containsPoints(array $haystack, array $needle): bool
    {
        foreach ($haystack as $candidate) {
            if (\count($candidate) !== \count($needle)) {
                continue;
            }

            $same = true;

            foreach ($needle as $index => [$x, $y]) {
                if (abs($candidate[$index][0] - $x) > self::TOLERANCE || abs($candidate[$index][1] - $y) > self::TOLERANCE) {
                    $same = false;

                    break;
                }
            }

            if ($same) {
                return true;
            }
        }

        return false;
    }
}
