<?php

declare(strict_types=1);

namespace Atelier\Pattern\Tests\Support;

use Atelier\Pattern\PatternInterface;
use Atelier\Svg\Element\ContainerElementInterface;
use Atelier\Svg\Element\ElementInterface;

/**
 * Reads the shapes back out of a built pattern, so tests can assert on the
 * geometry the renderer actually receives.
 */
final class Shapes
{
    /**
     * Every shape of one tile, groups flattened.
     *
     * @return list<ElementInterface>
     */
    public static function of(PatternInterface $pattern): array
    {
        return self::flatten($pattern->element());
    }

    /**
     * @return list<array{cx: float, cy: float, r: float}>
     */
    public static function circles(PatternInterface $pattern): array
    {
        $circles = [];

        foreach (self::of($pattern) as $shape) {
            if ('circle' !== $shape->getTagName()) {
                continue;
            }

            $circles[] = [
                'cx' => self::number($shape, 'cx'),
                'cy' => self::number($shape, 'cy'),
                'r' => self::number($shape, 'r'),
            ];
        }

        return $circles;
    }

    /**
     * @return list<array{x: float, y: float, width: float, height: float}>
     */
    public static function rects(PatternInterface $pattern): array
    {
        $rects = [];

        foreach (self::of($pattern) as $shape) {
            if ('rect' !== $shape->getTagName()) {
                continue;
            }

            $rects[] = [
                'x' => self::number($shape, 'x'),
                'y' => self::number($shape, 'y'),
                'width' => self::number($shape, 'width'),
                'height' => self::number($shape, 'height'),
            ];
        }

        return $rects;
    }

    /**
     * @return list<list<array{0: float, 1: float}>>
     */
    public static function polygons(PatternInterface $pattern): array
    {
        $polygons = [];

        foreach (self::of($pattern) as $shape) {
            if ('polygon' !== $shape->getTagName()) {
                continue;
            }

            $vertices = [];

            foreach (explode(' ', (string) $shape->getAttribute('points')) as $pair) {
                [$x, $y] = explode(',', $pair);
                $vertices[] = [(float) $x, (float) $y];
            }

            $polygons[] = $vertices;
        }

        return $polygons;
    }

    /**
     * The path data of every tile, split into subpaths, one string per "M".
     *
     * @return list<string>
     */
    public static function subpaths(PatternInterface $pattern): array
    {
        $subpaths = [];

        foreach (self::of($pattern) as $shape) {
            if ('path' !== $shape->getTagName()) {
                continue;
            }

            foreach (preg_split('/(?=M )/', (string) $shape->getAttribute('d'), -1, \PREG_SPLIT_NO_EMPTY) ?: [] as $subpath) {
                $subpaths[] = trim($subpath);
            }
        }

        return $subpaths;
    }

    /**
     * The numbers of a subpath, in order of appearance.
     *
     * @return list<float>
     */
    public static function numbersIn(string $subpath): array
    {
        preg_match_all('/-?\d+(?:\.\d+)?/', $subpath, $matches);

        return array_map(floatval(...), $matches[0]);
    }

    /**
     * The nodes of a subpath made of moves and lines only.
     *
     * @return list<array{0: float, 1: float}>
     */
    public static function pointsIn(string $subpath): array
    {
        $points = [];

        foreach (array_chunk(self::numbersIn($subpath), 2) as $pair) {
            $points[] = [$pair[0], $pair[1]];
        }

        return $points;
    }

    /**
     * @return list<ElementInterface>
     */
    private static function flatten(ElementInterface $element): array
    {
        if (!$element instanceof ContainerElementInterface) {
            return [$element];
        }

        $shapes = [];

        foreach ($element->getChildren() as $child) {
            if ('g' === $child->getTagName() || 'pattern' === $child->getTagName()) {
                $shapes = [...$shapes, ...self::flatten($child)];

                continue;
            }

            $shapes[] = $child;
        }

        return $shapes;
    }

    private static function number(ElementInterface $shape, string $name): float
    {
        return (float) $shape->getAttribute($name);
    }
}
