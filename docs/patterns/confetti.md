---
title: Confetti
description: Small shapes thrown across the surface, each with its own number of sides, size, and bearing.
order: 150
---

# Confetti

Confetti throws one small shape into each cell of a lattice, each with its own number of sides,
its own size and its own bearing. Take it for a scattered ground where no two marks should look
alike.

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

$confetti = Pattern::confetti(spacing: 20, size: 4, cells: 5, seed: 1);

$document = Document::create(360, 200);

(new PatternRegistry($confetti))->attachTo($document);

$surface = new RectElement();
$surface->setX('0')->setY('0')->setWidth('360')->setHeight('200');
$surface->setAttribute('fill', $confetti->fill());

$document->getRootElement()?->appendChild($surface);

echo (new CompactXmlDumper())->dump($document);
```

## Options

`Pattern::confetti()` takes its geometry in user units, plus the size of the repeating tile and
the draw. Style applies afterwards, and each call returns a new tile.

| Setting | Default | Effect |
|---|---|---|
| `spacing` | `20` | side of the cell one shape lands in |
| `size` | `4` | distance from the middle of a shape to its farthest vertex, at most half the spacing |
| `cells` | `5` | cells per side of the repeating tile, at least 2; the period is `cells * spacing` |
| `seed` | `1` | where each shape lands, how many sides it has, how large it is, and which way it faces |
| `withColor(string)` | `currentColor` | paint of the shapes; they are filled, never outlined |
| `withOpacity(?float)` | none | opacity of the tile, applied to the whole drawing at once, so two shapes that overlap keep one flat tone |
| `withAngle(float)` | `0` | turns the whole scatter through `patternTransform`; it does not reshuffle it |
| `withId(string)` | derived | fixes the identifier instead of deriving it from the tile content |

A size above half the spacing, or a cell count below 2, throws `InvalidArgumentException`.

The same inputs and `seed` draw the same tiling within the same generator version. It takes part in the derived identifier,
so two seeds are two definitions in the `<defs>` rather than one.

## How the tile closes

The repeating tile is a square of `cells` by `cells` cells of one spacing, so the period is
`cells * spacing` on both axes and the disorder stops at its border.

One shape lands anywhere in its cell. It is dealt three to five sides, a radius between 55
percent of `size` and `size`, and a bearing anywhere on the circle, which is why no two marks
repeat inside a tile.

A shape landing on a border overhangs the tile, so it is drawn a second time one period away
against the opposite border, and a shape landing on a corner is drawn on all four. Whatever the
clip cuts off comes back on the other side.
