---
title: Honeycomb
description: An outlined hexagonal mesh, hexagons standing on a point, for a cell structure or a map ground.
order: 70
---

# Honeycomb

Honeycomb outlines a hexagonal mesh, every hexagon standing on a point. Take it for a cell
structure, a map ground, or a texture that fills a surface without any direction dominating it.

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

$honeycomb = Pattern::honeycomb(radius: 12, thickness: 1.1);

$document = Document::create(360, 200);

(new PatternRegistry($honeycomb))->attachTo($document);

$surface = new RectElement();
$surface->setX('0')->setY('0')->setWidth('360')->setHeight('200');
$surface->setAttribute('fill', $honeycomb->fill());

$document->getRootElement()?->appendChild($surface);

echo (new CompactXmlDumper())->dump($document);
```

## Options

`Pattern::honeycomb()` takes two measurements in user units. Style applies afterwards, and each
call returns a new tile.

| Setting | Default | Effect |
|---|---|---|
| `radius` | `12` | distance from a hexagon centre to a vertex; the hexagon is `sqrt(3) * radius` wide and `2 * radius` tall |
| `thickness` | `1.1` | line width, at most the radius |
| `withColor(string)` | `currentColor` | stroke of the mesh; the hexagons are outlined, never filled |
| `withOpacity(?float)` | none | opacity of the tile, applied to the whole drawing at once, so a shared wall keeps the same tone as a free one |
| `withAngle(float)` | `0` | turns the mesh; at `30` the hexagons stand on a side instead of a point |
| `withId(string)` | derived | fixes the identifier instead of deriving it from the tile content |

A thickness above the radius throws `InvalidArgumentException`.

## How the tile closes

Centres sit `sqrt(3) * radius` apart across and `1.5 * radius` apart down, with every other row
moved half a width, so the smallest tile that comes back to itself is `sqrt(3) * radius` by
`3 * radius` and holds two rows.

Five hexagons are drawn into it. One is centred at the middle of the tile and stays inside it.
The other four are a single lattice point, the corner, drawn against all four corners: `(0, 0)`,
`(w, 0)`, `(0, h)` and `(w, h)` are the same point one period apart, so whatever the clip cuts
off one of them is exactly what another puts back.

Walls are shared. Two neighbouring hexagons are stroked along the same segment, which is why the
mesh reads as one line rather than as two touching outlines.
