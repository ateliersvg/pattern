---
title: Truchet
description: Quarter circles turned at random in every cell, joining into one tangle of continuous curves.
order: 110
---

# Truchet

Truchet turns a pair of quarter circles at random in every cell of a grid. The curves join
whatever they were dealt, so the surface reads as one continuous tangle. Take it for a dense
texture of loops. Its macro tile repeats every `cells * tile` units; small cell counts make
that repetition easier to see.

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

$truchet = Pattern::truchet(tile: 24, thickness: 2.5, cells: 8, seed: 1);

$document = Document::create(360, 200);

(new PatternRegistry($truchet))->attachTo($document);

$surface = new RectElement();
$surface->setX('0')->setY('0')->setWidth('360')->setHeight('200');
$surface->setAttribute('fill', $truchet->fill());

$document->getRootElement()?->appendChild($surface);

echo (new CompactXmlDumper())->dump($document);
```

## Options

`Pattern::truchet()` takes its geometry in user units, plus the size of the repeating tile and
the draw. Style applies afterwards, and each call returns a new tile.

| Setting | Default | Effect |
|---|---|---|
| `tile` | `24` | side of one cell, and the radius of a quarter circle is half of it |
| `thickness` | `2.5` | line width, at most half a cell |
| `cells` | `8` | cells per side of the repeating tile, at least 2; the period is `cells * tile` |
| `seed` | `1` | which way each cell is turned |
| `withColor(string)` | `currentColor` | stroke of the curves; they are outlined, never filled |
| `withOpacity(?float)` | none | opacity of the tile, applied to the whole drawing at once, so a junction keeps the same tone as a free curve |
| `withAngle(float)` | `0` | turns the whole paving through `patternTransform` |
| `withId(string)` | derived | fixes the identifier instead of deriving it from the tile content |

A thickness above half a cell throws `InvalidArgumentException`, and so does a cell count below
2: one cell has nothing to disorder.

The same inputs and `seed` draw the same tiling within the same generator version. It takes part in the derived identifier,
so two seeds are two definitions in the `<defs>` rather than one.

## How the tile closes

A cell carries two quarter circles of radius half a cell, centred on two opposite corners. Which
of the two diagonals they sit on is drawn at random, and that is the whole of the disorder.

Either way the four ends land on the four edge midpoints of the cell, and each one meets the
shared edge at a right angle. Two neighbouring cells therefore join whatever they were dealt,
because both offer an end at the same point and at the same angle.

The repeating tile is a square of `cells` by `cells` cells. Its border is a row of edge
midpoints like any other, no curve crosses it, and nothing has to be drawn twice: the disorder
stops at the border and every tile presents the same joins to the next.
