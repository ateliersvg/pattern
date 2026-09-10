---
title: Scales
description: Rows of overlapping half ellipses laid out like fish scales, for water, a roof, or a feathered surface.
order: 80
---

# Scales

Scales lays half ellipses in rows, each row moved half a width against the one above, so the
arcs overlap the way scales do. Take it for water, a roof, or any surface that should read as
layered.

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

$scales = Pattern::scales(width: 18, height: 12, thickness: 1);

$document = Document::create(360, 200);

(new PatternRegistry($scales))->attachTo($document);

$surface = new RectElement();
$surface->setX('0')->setY('0')->setWidth('360')->setHeight('200');
$surface->setAttribute('fill', $scales->fill());

$document->getRootElement()?->appendChild($surface);

echo (new CompactXmlDumper())->dump($document);
```

## Options

`Pattern::scales()` takes three measurements in user units. Style applies afterwards, and each
call returns a new tile.

| Setting | Default | Effect |
|---|---|---|
| `width` | `18` | span of one arc, and the width of the tile |
| `height` | `12` | rise of one arc, and the distance between two rows |
| `thickness` | `1` | line width, at most the height |
| `withColor(string)` | `currentColor` | stroke of the arcs; they are outlined, never filled |
| `withOpacity(?float)` | none | opacity of the tile, applied to the whole drawing at once, so an arc crossing another keeps one flat tone |
| `withAngle(float)` | `0` | turns the rows; at `180` the arcs open upwards |
| `withId(string)` | derived | fixes the identifier instead of deriving it from the tile content |

A thickness above the height throws `InvalidArgumentException`.

## How the tile closes

Rows sit one `height` apart and move half a `width` from one row to the next, so an arc apex
lands on the junction of the two arcs above it. The offset only cancels after two rows, which is
why the tile is `width` by `2 * height`.

The aligned row crosses the middle of the tile, from one vertical edge to the other, so its two
ends complete each other across the seam.

The shifted row straddles the horizontal edges, so it is drawn twice: once at
`0.5 * height` and once at `2.5 * height`, one full period apart. Each of those is two arcs, one
starting half a width to the left of the tile and one half a width inside it, so the piece cut
off at one vertical edge comes back at the other.
