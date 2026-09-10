---
title: Chevron
description: Stacked zigzags with their peaks aligned, for a surface that has to carry a direction.
order: 90
---

# Chevron

Chevron stacks zigzags with their peaks aligned from one row to the next. Take it when the
surface needs a direction: the zigzag points one way, and `withAngle()` points it anywhere else.

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

$chevron = Pattern::chevron(size: 20, thickness: 2);

$document = Document::create(360, 200);

(new PatternRegistry($chevron))->attachTo($document);

$surface = new RectElement();
$surface->setX('0')->setY('0')->setWidth('360')->setHeight('200');
$surface->setAttribute('fill', $chevron->fill());

$document->getRootElement()?->appendChild($surface);

echo (new CompactXmlDumper())->dump($document);
```

## Options

`Pattern::chevron()` takes two measurements in user units. Style applies afterwards, and each
call returns a new tile.

| Setting | Default | Effect |
|---|---|---|
| `size` | `20` | distance between two peaks of the same row, and the width of the tile |
| `thickness` | `2` | line width, at most half the size |
| `withColor(string)` | `currentColor` | stroke of the zigzags |
| `withOpacity(?float)` | none | opacity of the tile, applied to the whole drawing at once, so the tips a neighbouring row contributes keep the same tone as the row itself |
| `withAngle(float)` | `0` | turns the rows; at `90` the chevrons point sideways |
| `withId(string)` | derived | fixes the identifier instead of deriving it from the tile content |

A thickness above half the size throws `InvalidArgumentException`.

## How the tile closes

One zigzag is `size` wide and half a size tall, and the rows repeat every half size, so the tile
is `size` by `size / 2` and holds one row.

Peaks and troughs land on the horizontal edges, where a single row would be cut into flat ends.
The rows above and below are drawn as well, at baselines one tile height either side. Clipped to
the tile they contribute only the tips their neighbours cut off, which is what keeps the points
sharp across the seam.

Each zigzag also runs half a period past both vertical edges, from `-size / 2` to
`1.5 * size`. A corner needs both of its segments to be mitred, and a segment that stopped on
the edge would be capped instead.
