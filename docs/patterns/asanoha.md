---
title: Asanoha
description: The hemp leaf motif, six pointed stars on a triangular lattice.
order: 97
---

# Asanoha

Asanoha is the hemp leaf: a hexagon split into six triangles, each carrying three lines from its
corners to its centre, repeated across a triangular lattice. The result reads as a field of six
pointed stars. Take it for a fine, even mesh with more structure than a crosshatch.

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

$asanoha = Pattern::asanoha(size: 28, thickness: 1.2);

$document = Document::create(360, 200);

(new PatternRegistry($asanoha))->attachTo($document);

$surface = new RectElement();
$surface->setX('0')->setY('0')->setWidth('360')->setHeight('200');
$surface->setAttribute('fill', $asanoha->fill());

$document->getRootElement()?->appendChild($surface);

echo (new CompactXmlDumper())->dump($document);
```

## Options

| Setting | Default | Effect |
|---|---|---|
| `size` | `28` | radius of one hexagon, so the distance from a star centre to its points |
| `thickness` | `1.2` | line width, at most a quarter of the size |
| `withColor(string)` | `currentColor` | stroke of the lattice; it is outlined, never filled |
| `withOpacity(?float)` | none | opacity of the whole tile at once, so a crossing keeps the tone of a free line |
| `withAngle(float)` | `0` | turns the whole paving through `patternTransform` |
| `withId(string)` | derived | fixes the identifier instead of deriving it from the tile content |

A thickness above a quarter of the size throws `InvalidArgumentException`: past that the lines
of a star merge and the figure closes up.

## How the tile closes

The lattice is triangular, so the smallest repeat is the same rectangle a honeycomb of the same
radius uses, one hexagon wide and two rows tall.

Every line of the drawing runs between two lattice points, and lattice points on one edge have
their translate on the opposite edge by construction. A line crossing the right edge is
therefore drawn again from the matching point on the left edge, and the two segments continue
each other at the same angle. Stars sitting on a corner appear at all four corners, each
contributing the quarter the clip leaves of it.
