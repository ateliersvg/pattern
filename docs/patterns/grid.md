---
title: Grid
description: Square cells ruled by one horizontal and one vertical line, for a plan or a background measure.
order: 50
---

# Grid

Grid rules square cells with one horizontal and one vertical line. Take it for a plan, a drawing
ground, or any background that has to carry a measure.

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

$grid = Pattern::grid(size: 18, thickness: 1);

$document = Document::create(360, 200);

(new PatternRegistry($grid))->attachTo($document);

$surface = new RectElement();
$surface->setX('0')->setY('0')->setWidth('360')->setHeight('200');
$surface->setAttribute('fill', $grid->fill());

$document->getRootElement()?->appendChild($surface);

echo (new CompactXmlDumper())->dump($document);
```

## Options

`Pattern::grid()` takes two measurements in user units. Style applies afterwards, and each call
returns a new tile.

| Setting | Default | Effect |
|---|---|---|
| `size` | `18` | cell side, and the side of the tile |
| `thickness` | `1` | rule width, at most the cell side |
| `withColor(string)` | `currentColor` | paint of both rules |
| `withOpacity(?float)` | none | opacity of the tile, applied to the whole drawing at once, so the crossing keeps the same tone as the rules |
| `withAngle(float)` | `0` | turns the ruling through `patternTransform` |
| `withId(string)` | derived | fixes the identifier instead of deriving it from the tile content |

A thickness above the cell side throws `InvalidArgumentException`.

## How the tile closes

The tile is a square of the cell side, and the period is that side on both axes.

The two rules are filled rectangles anchored on the top and left edges, not centred strokes. A
stroke sitting on an edge loses half its width to the clip and renders thinner than asked; a
rectangle anchored inside the tile keeps the width you gave it.

That leaves the right and the bottom of a cell unruled, and they stay that way. They are the
left and the top rules of the tiles next to it, so every rule is drawn once and shared with a
neighbour, and nothing crosses a boundary.
