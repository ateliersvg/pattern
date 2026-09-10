---
title: Voronoi
description: A repeating Voronoi partition with convex cells, optional fills and wrapped borders.
order: 160
---

# Voronoi

Scatter a handful of points, then give every part of the surface to its nearest point. The
borders of those territories are a Voronoi partition: convex cells of uneven size and shape,
typically meeting three at an interior vertex when the sites are in general position. Take it for an organic mesh, closer to cracked glaze or
a cell wall than to anything ruled.

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

$voronoi = Pattern::voronoi(size: 120, thickness: 1.4, sites: 14, seed: 1);

$document = Document::create(360, 200);

(new PatternRegistry($voronoi))->attachTo($document);

$surface = new RectElement();
$surface->setX('0')->setY('0')->setWidth('360')->setHeight('200');
$surface->setAttribute('fill', $voronoi->fill());

$document->getRootElement()?->appendChild($surface);

echo (new CompactXmlDumper())->dump($document);
```

## Options

| Setting | Default | Effect |
|---|---|---|
| `size` | `120` | side of the repeating tile, so how far the eye travels before the partition comes round again |
| `thickness` | `1.4` | line width, at most a quarter of the size |
| `sites` | `14` | how many points share the tile, at least 2; more points means smaller cells |
| `seed` | `1` | where the points fall |
| `filled` | `false` | paints each cell as well as stroking it |
| `minOpacity` | `0.15` | opacity of the palest cell when filled |
| `maxOpacity` | `0.65` | opacity of the darkest cell when filled |
| `withColor(string)` | `currentColor` | paint of the borders and, with `filled: true`, the cell fills |
| `withOpacity(?float)` | none | opacity of the whole tile at once |
| `withAngle(float)` | `0` | turns the whole paving through `patternTransform` |
| `withId(string)` | derived | fixes the identifier instead of deriving it from the tile content |

The same inputs and `seed` draw the same partition within the same generator version. It takes part in the derived
identifier, so two seeds are two definitions in the `<defs>` rather than one.

Keep `size` well above the other tiles of this catalogue. A Voronoi has no small repeat: the
period is the tile, and a tile of 40 reads as a grid of identical clusters.

A filled cell takes its opacity from its own seed, not from the order it happens to be drawn
in. A seed replicated onto a neighbouring tile is the same seed, so the two halves of a cell cut
by an edge carry the same tone and the join stays invisible in colour as well as in shape.

## How the tile closes

The partition is computed on a torus rather than on a square. Each of the `sites` points is
copied onto the eight neighbouring tiles, and every cell is cut against that whole field of
points, not only against the points inside the tile.

A cell touching the right edge is therefore bounded by a point living beyond it, which is the
same point that bounds its counterpart against the left edge. The two halves are cut by the same
bisector, at the same angle, so the border continues across the seam as one straight edge.
The SVG includes translated copies of cells that cross a boundary. The toroidal calculation
makes their geometry match; those copies supply the visible pieces on each side of the tile.
