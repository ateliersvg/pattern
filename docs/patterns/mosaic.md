---
title: Mosaic
description: A Voronoi partition read on a grid, square cells taking the tone of their nearest seed.
order: 170
---

# Mosaic

Throw a handful of seeds, lay a grid over them, and give each square the tone of the seed nearest
its middle. The territories of a Voronoi partition come back, but their borders now follow the
grid instead of cutting across it: no diagonal, no vertex where three cells meet, only steps.
Read it as tesserae set by hand, or as a map whose regions were sampled rather than traced.

## Example

```php
<?php

declare(strict_types=1);

use Atelier\Pattern\Pattern;
use Atelier\Pattern\PatternRegistry;
use Atelier\Svg\Document;
use Atelier\Svg\Dumper\CompactXmlDumper;
use Atelier\Svg\Element\Shape\RectElement;

require __DIR__.'/vendor/autoload.php';

$mosaic = Pattern::mosaic(size: 150, cells: 24, sites: 14, tones: 7, seed: 1);

$document = Document::create(360, 200);

(new PatternRegistry($mosaic))->attachTo($document);

$surface = new RectElement();
$surface->setX('0')->setY('0')->setWidth('360')->setHeight('200');
$surface->setAttribute('fill', $mosaic->fill());

$document->getRootElement()?->appendChild($surface);

echo (new CompactXmlDumper())->dump($document);
```

## Options

| Setting | Default | Effect |
|---|---|---|
| `size` | `150` | side of the repeating tile, so how far the eye travels before the map comes round again |
| `cells` | `24` | squares across the tile, at least 2; more cells means finer steps along a border |
| `sites` | `14` | seeds sharing the tile, at least 2; more seeds means smaller territories |
| `tones` | `7` | distinct tones in the tiling, at least 2 and at most `sites` |
| `seed` | `1` | where the seeds fall and which tone each one takes |
| `grid` | `true` | draws the rules between the squares |
| `seeds` | `true` | marks each seed with a dot, which reads as the centre of its territory |
| `withColor(string)` | `currentColor` | the one ink every tone is an opacity of |
| `withOpacity(?float)` | none | opacity of the whole tile at once |
| `withAngle(float)` | `0` | turns the whole paving through `patternTransform` |
| `withId(string)` | derived | fixes the identifier instead of deriving it from the tile content |

`cells` and `size` are read together: a square is `size / cells` wide, and a square below about
three units reads as noise rather than as masonry.

`tones` is the number of opacities the squares are painted at, spread evenly between 0.15 and
0.65, the bounds a filled voronoi uses. A square takes the index of its seed modulo `tones`, not
a value derived from how far that seed is, so the surface reads as a few flat tones rather than
as a continuum. Two neighbouring territories can land on the same tone and merge into one shape,
which is what keeps the tiling from looking mechanically striped.

## How the tile closes

The distance from a square to a seed is measured on the torus, not in the plane: along each
axis the shorter of the two ways is kept, straight across the tile or out through one edge and
back in through the other. A square on the right edge can therefore belong to a seed living past
the left one.

A territory reaching over an edge is therefore continued by the squares of the opposite edge,
which measure themselves against the same seed and take the same tone. Nothing is drawn twice
and no square is clipped, since the grid divides the tile exactly. The map closes because of
where the distance was measured, not because a correction was applied afterwards.

The same `seed` draws the same map, run after run. It takes part in the derived identifier, so
two seeds are two definitions in the `<defs>` rather than one.
