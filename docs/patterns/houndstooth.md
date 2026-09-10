---
title: Houndstooth
description: The broken check of woven wool, filled shapes whose pointed teeth interlock across the surface.
order: 95
---

# Houndstooth

Houndstooth is what a two by two twill does to a two by two check: the weave drags each corner
into a point, and the points of one colour reach into the field of the other. Take it for a
surface that reads as cloth, at any size from a background texture to a bold graphic.

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

$houndstooth = Pattern::houndstooth(size: 24);

$document = Document::create(360, 200);

(new PatternRegistry($houndstooth))->attachTo($document);

$surface = new RectElement();
$surface->setX('0')->setY('0')->setWidth('360')->setHeight('200');
$surface->setAttribute('fill', $houndstooth->fill());

$document->getRootElement()?->appendChild($surface);

echo (new CompactXmlDumper())->dump($document);
```

## Options

| Setting | Default | Effect |
|---|---|---|
| `size` | `24` | side of the repeating tile; the weave sits on a four by four grid of quarters of it |
| `thickness` | `0` | outlines the shapes instead of filling them; `0` fills |
| `withColor(string)` | `currentColor` | the single colour that is painted |
| `withOpacity(?float)` | none | opacity of the whole tile at once |
| `withAngle(float)` | `0` | turns the whole paving through `patternTransform` |
| `withId(string)` | derived | fixes the identifier instead of deriving it from the tile content |

A thickness above a quarter of the size throws `InvalidArgumentException`.

Only one colour is ever painted. The second colour of a houndstooth is the surface showing
through, which is why the tile changes character with what you put it on, and why it follows a
theme instead of fighting it.

## How the tile closes

The tile is one full repeat of the weave, `size` by `size`, laid out on a grid of sixteen
quarters. Two solid squares of two quarters sit on a diagonal, and each one grows a tooth at
two opposite corners: a triangle reaching one quarter into the neighbouring field.

Those teeth are what crosses the edges. A tooth leaving through the right edge is drawn again
against the left edge, one period away, and the same holds top to bottom. The check underneath
is periodic on its own, so the two halves of every tooth meet exactly, and the eye reads an
unbroken point rather than two triangles that happen to touch.
