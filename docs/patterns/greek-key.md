---
title: Greek key
description: The Greek fret, a key spiralling off a continuous rule.
order: 102
---

# Greek key

Greek key runs a meander along a rule: one line turning back on itself, repeated. Take it for a
border, a banded ground, or any surface that should read as classical.

This fret is a running ornament, and this is the only tile in the catalogue drawn
as a single continuous figure rather than as a motif on a lattice.

The rule matters as much as the key. Without it the keys stand apart and read as a row of stamps;
with it the row reads as one band, which is what the ornament is for.

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

$key = Pattern::greekKey(size: 40, thickness: 1.4);

$document = Document::create(360, 200);

(new PatternRegistry($key))->attachTo($document);

$surface = new RectElement();
$surface->setX('0')->setY('0')->setWidth('360')->setHeight('200');
$surface->setAttribute('fill', $key->fill());

$document->getRootElement()?->appendChild($surface);

echo (new CompactXmlDumper())->dump($document);
```

## Options

| Setting | Default | Effect |
|---|---|---|
| `size` | `40` | side of the tile, eight units; the key is drawn on that grid |
| `thickness` | `1.4` | line width, at most one unit |
| `withColor(string)` | `currentColor` | paint of the line |
| `withOpacity(?float)` | none | opacity of the whole tile |
| `withAngle(float)` | `0` | turns the band through `patternTransform`; 90 degrees stands it as a column |
| `withId(string)` | derived | fixes the identifier instead of deriving it from the tile content |

The proportions are fixed at eighths of the tile, so `size` scales the whole figure and the key
keeps its shape. A thickness of a full unit closes the spiral into a solid block, which is why the
guard stops there.

## How the tile closes

The tile is square. The rule runs the full width at seven eighths of the way down, ending exactly
on both edges, and the key stands one unit inside the tile on every side.

Nothing crosses an edge, so no shape is drawn twice. The rule of one tile continues into the rule
of the next, and a row of keys comes out of it.
