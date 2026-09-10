---
title: Triangles
description: The third regular tessellation, one ink for the standing triangles and one for the field they stand on.
order: 60
---

# Triangles

Triangles cuts the surface into equilateral triangles, one orientation inked over the field of
the other. Take it for a faceted ground, a backdrop under a chart, or any surface that should
read as cut rather than woven.

Three regular polygons tile the plane on their own: the square, the hexagon and the triangle.
`grid` draws the first and `honeycomb` the second, and this is the third.
A strip alternates a triangle standing on its base with one hanging from the line above, and the
strip below repeats it half a side across, because the vertices of the two strips have to meet.

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

$triangles = Pattern::triangles(size: 24, up: 1.0, down: 0.3);

$document = Document::create(360, 200);

(new PatternRegistry($triangles))->attachTo($document);

$surface = new RectElement();
$surface->setX('0')->setY('0')->setWidth('360')->setHeight('200');
$surface->setAttribute('fill', $triangles->fill());

$document->getRootElement()?->appendChild($surface);

echo (new CompactXmlDumper())->dump($document);
```

## Options

| Setting | Default | Effect |
|---|---|---|
| `size` | `24` | side of one triangle; the tile is one side wide and two strips tall |
| `up` | `1` | opacity of the standing triangle overlay, `[0,1]` |
| `down` | `0.3` | opacity of the full tile background underneath, `[0,1]` |
| `withColor(string)` | `currentColor` | paint of both |
| `withOpacity(?float)` | none | opacity of the whole tile |
| `withAngle(float)` | `0` | turns the tiling through `patternTransform` |
| `withId(string)` | derived | fixes the identifier instead of deriving it from the tile content |

The settings control two paint layers, not independent final face opacities. Away from
antialiased edges, the standing faces have alpha `up + down * (1 - up)` and the hanging faces
have alpha `down`, before `withOpacity()` applies to the group. With both set to `0.5`, those
values are `0.75` and `0.5`: equal intermediate settings do not produce a flat tone.
A `down` of `0` leaves the hanging faces transparent. `down: 1` gives one opaque tone.

The hanging triangles are not drawn as shapes. The tile is painted with `down` from edge to edge
and the standing triangles go on top at `up`. This avoids separate adjacent face fills. Two fills
that share an edge leave a hairline between them, because a renderer antialiases each one against
what was already there, and a lattice of those hairlines reads as a ruled pattern nobody asked
for. One field has no interior edge to show.

## How the tile closes

The tile is one side wide and two strip heights tall, a strip standing `size * sqrt(3) / 2`.

Each strip contributes one standing triangle. The one in the lower strip has its apex on the left
edge, so half of it hangs outside the tile and it is drawn again a period to the right. The field
underneath is the tile itself and needs no copy.
