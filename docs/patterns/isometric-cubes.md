---
title: Isometric cubes
description: Rhombs shaded on three faces so a flat surface reads as a stack of cubes seen from above.
order: 96
---

# Isometric cubes

Three rhombs meet at every vertex of a hexagonal lattice. Shade them as a top, a left and a
right face and the flat surface lifts into a stack of cubes. Nothing is drawn in perspective:
the volume is entirely in the three tones.

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

$cubes = Pattern::isometricCubes(size: 22);

$document = Document::create(360, 200);

(new PatternRegistry($cubes))->attachTo($document);

$surface = new RectElement();
$surface->setX('0')->setY('0')->setWidth('360')->setHeight('200');
$surface->setAttribute('fill', $cubes->fill());

$document->getRootElement()?->appendChild($surface);

echo (new CompactXmlDumper())->dump($document);
```

## Options

| Setting | Default | Effect |
|---|---|---|
| `size` | `22` | edge of one rhomb, so the apparent size of a cube |
| `topFace` | `0.3` | opacity of the upper face |
| `leftFace` | `0.85` | opacity of the left face |
| `rightFace` | `0.55` | opacity of the right face |
| `withColor(string)` | `currentColor` | the single hue the three faces share |
| `withOpacity(?float)` | none | opacity of the whole tile, multiplied into the three faces |
| `withAngle(float)` | `0` | turns the whole paving through `patternTransform` |
| `withId(string)` | derived | fixes the identifier instead of deriving it from the tile content |

Each face opacity must lie between 0 and 1, and throws `InvalidArgumentException` otherwise.

One hue, three opacities. The volume survives a change of colour and a change of theme, because
it is carried by the contrast between the faces and not by the colours themselves. Move the
three values closer together and the cubes flatten back into a lattice of rhombs.

## How the tile closes

The rhombs form the rhombille tiling, the dual of the trihexagonal lattice: every hexagon of a
honeycomb split into three rhombs meeting at its centre. Its smallest repeat is the same
rectangle a honeycomb of the same size uses, one hexagon wide and two rows tall.

Rhombs cross the edges wherever a hexagon straddles them, so the tile draws the five lattice
positions a honeycomb draws: the one that fits, and the four sitting on the edges, each present
on both sides. A rhomb cut by the right edge finds its other half against the left edge, with
the same face shading, so the cube it belongs to never changes tone across a seam.
