---
title: Quatrefoil
description: Four lobes to a rosette, on a lattice that carries a second rosette in the middle of every cell.
order: 101
---

# Quatrefoil

Quatrefoil sets four discs on the arms of a cross, close enough to merge into one rosette, on a
lattice that carries a second rosette in the middle of every cell. Take it for a screen, a tiled
wall, or any ground that should read as cut rather than printed.

Filling the four discs with one ink is what unions them. No boolean operation runs, and the
outline nobody draws is the one the eye reads.

What the rosettes leave between them is the subject as much as the rosettes themselves. The gap
closes into the pointed ogee the tiling is named for, and it is why the second rosette sits in the
middle of the cell rather than on its own row.

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

$quatrefoil = Pattern::quatrefoil(size: 34, lobe: 6.4);

$document = Document::create(360, 200);

(new PatternRegistry($quatrefoil))->attachTo($document);

$surface = new RectElement();
$surface->setX('0')->setY('0')->setWidth('360')->setHeight('200');
$surface->setAttribute('fill', $quatrefoil->fill());

$document->getRootElement()?->appendChild($surface);

echo (new CompactXmlDumper())->dump($document);
```

## Options

| Setting | Default | Effect |
|---|---|---|
| `size` | `34` | side of the lattice cell; the tile is one cell and carries two rosettes |
| `lobe` | `6.4` | radius of one lobe, at most a quarter of the cell |
| `withColor(string)` | `currentColor` | paint of the rosettes; the ogee between them stays transparent |
| `withOpacity(?float)` | none | opacity of the whole tile |
| `withAngle(float)` | `0` | turns the screen through `patternTransform` |
| `withId(string)` | derived | fixes the identifier instead of deriving it from the tile content |

The ratio of lobe to cell is the whole design. Small lobes give a scatter of quatrefoils on an
open ground; at a quarter of the cell they meet and the ogee closes to a line. The guard stops
there, because past it the rosettes merge and the surface goes solid.

A lobe sits `0.92` of its own radius from the middle of its rosette. Closer and the four discs
read as one blob, further and they come apart into four dots.

## How the tile closes

The tile is one cell, and holds two rosettes: one on its corner and one in its middle. Eight
lobes, twenty circles once the copies the edges call for are counted.

Every lobe on the corner rosette crosses an edge, and the four of the middle rosette stay inside.
