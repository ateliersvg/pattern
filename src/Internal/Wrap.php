<?php

declare(strict_types=1);

namespace Atelier\Pattern\Internal;

/**
 * Where a shape has to be drawn a second time for the tile to join with itself.
 *
 * A shape overhanging an edge is cut by the clip, and the cut piece has to come
 * from the tile itself, one period away against the opposite edge. Given an
 * envelope, this returns every offset the shape must be repeated at, starting
 * with the shape itself.
 *
 * @internal
 */
final class Wrap
{
    /**
     * @param float $minX left of the envelope, half a stroke width included
     *
     * @return list<array{float, float}> offsets to draw the shape at, (0, 0) first
     */
    public static function offsets(float $minX, float $minY, float $maxX, float $maxY, float $width, float $height): array
    {
        $columns = [0.0];

        if ($minX < 0.0) {
            $columns[] = $width;
        }

        if ($maxX > $width) {
            $columns[] = -$width;
        }

        $rows = [0.0];

        if ($minY < 0.0) {
            $rows[] = $height;
        }

        if ($maxY > $height) {
            $rows[] = -$height;
        }

        $offsets = [];

        // A shape crossing two edges at once is cut on a corner, so the corner
        // neighbour needs its copy too.
        foreach ($columns as $dx) {
            foreach ($rows as $dy) {
                $offsets[] = [$dx, $dy];
            }
        }

        return $offsets;
    }
}
