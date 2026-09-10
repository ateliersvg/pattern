---
title: Checker
description: Alternating filled and empty cells, with the surface underneath showing through the empty half.
order: 100
---

# Checker

Checker alternates filled and empty cells. Only half the surface is painted: the other half
carries no shape at all, so whatever sits underneath shows through it. Take it for a
transparency ground, a board, or any two-tone surface where the second tone is the page itself.

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

$checker = Pattern::checker(size: 12);

$document = Document::create(360, 200);

(new PatternRegistry($checker))->attachTo($document);

$surface = new RectElement();
$surface->setX('0')->setY('0')->setWidth('360')->setHeight('200');
$surface->setAttribute('fill', $checker->fill());

$document->getRootElement()?->appendChild($surface);

echo (new CompactXmlDumper())->dump($document);
```

## Options

`Pattern::checker()` takes one measurement in user units. Style applies afterwards, and each call
returns a new tile.

| Setting | Default | Effect |
|---|---|---|
| `size` | `12` | side of one cell; the tile is twice that on both axes |
| `withColor(string)` | `currentColor` | paint of the filled cells; the empty ones stay transparent |
| `withOpacity(?float)` | none | opacity of the tile; at a value below `1` the filled cells tint the surface instead of covering it |
| `withAngle(float)` | `0` | turns the board through `patternTransform` |
| `withId(string)` | derived | fixes the identifier instead of deriving it from the tile content |

There is no thickness and no second colour: the pattern is the two filled cells, and the rest is
the surface. `size` must be a finite number greater than zero, and nothing else is checked,
because nothing else can fail to tile.

## How the tile closes

A checkerboard only comes back to itself after two cells, so the tile spans `2 * size` on both
axes.

Two squares are drawn into it, at `(0, 0)` and `(size, size)`. Both sit entirely inside the
tile, meeting the boundary without crossing it, so nothing is drawn twice.

The two remaining cells hold no shape. They are not painted with a background colour, they are
empty, which is what lets the surface underneath read through them.
