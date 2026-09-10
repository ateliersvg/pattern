---
title: Flower of life
description: Circles spaced by their own radius on a triangular lattice, so every one passes through the middles of six others.
order: 99
---

# Flower of life

Flower of life draws circles on a triangular lattice, spaced by their own radius. Take it for an
ornament ground, a geometric backdrop, or any surface that should read as drawn with a compass.

That single spacing is the whole figure: a circle passes through the middles of the six around it,
and the overlaps close into the six petal rosette the pattern is known for.

It stands on the lattice `asanoha` and `honeycomb` already use, so the
three sit together on a page and read as a family.

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

$flower = Pattern::flowerOfLife(radius: 25, thickness: 1);

$document = Document::create(360, 200);

(new PatternRegistry($flower))->attachTo($document);

$surface = new RectElement();
$surface->setX('0')->setY('0')->setWidth('360')->setHeight('200');
$surface->setAttribute('fill', $flower->fill());

$document->getRootElement()?->appendChild($surface);

echo (new CompactXmlDumper())->dump($document);
```

## Options

| Setting | Default | Effect |
|---|---|---|
| `radius` | `25` | radius of one circle, which is also the spacing between two of them |
| `thickness` | `1` | line width, at most an eighth of the radius |
| `withColor(string)` | `currentColor` | paint of the circles |
| `withOpacity(?float)` | none | opacity of the whole tile |
| `withAngle(float)` | `0` | turns the lattice through `patternTransform` |
| `withId(string)` | derived | fixes the identifier instead of deriving it from the tile content |

There is one measurement, because there is only one to make. Spacing the circles at anything but
their radius gives a mesh of circles, and the rosette is gone.

## How the tile closes

The tile is one radius wide and `radius * sqrt(3)` tall, and holds one circle per row of the
lattice: one at its corner and one half a step across and half a row down.

A circle is two tiles wide, so each one leaves the tile on every side and is drawn again on the
far side of every edge it crosses, corners included. Thirteen circles are emitted for the two the
tile owns, which is what this geometry costs to join.
