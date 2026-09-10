---
title: Crosshatch
description: Two diagonals crossing in every cell, for shading that darkens a surface without filling it.
order: 40
---

# Crosshatch

Crosshatch crosses two diagonals inside one square cell. Take it to darken a surface without
covering it: the mesh reads as shading, and the spacing sets how dark it goes.

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

$crosshatch = Pattern::crosshatch(spacing: 12, thickness: 1.2);

$document = Document::create(360, 200);

(new PatternRegistry($crosshatch))->attachTo($document);

$surface = new RectElement();
$surface->setX('0')->setY('0')->setWidth('360')->setHeight('200');
$surface->setAttribute('fill', $crosshatch->fill());

$document->getRootElement()?->appendChild($surface);

echo (new CompactXmlDumper())->dump($document);
```

## Options

`Pattern::crosshatch()` takes two measurements in user units. Style applies afterwards, and each
call returns a new tile.

| Setting | Default | Effect |
|---|---|---|
| `spacing` | `12` | side of the square tile; perpendicular spacing between parallel diagonals is `spacing / sqrt(2)` |
| `thickness` | `1.2` | line width, at most the spacing |
| `angle` | `0` | turns the tile at construction; the same result as `withAngle()`, given where the geometry is chosen |
| `withColor(string)` | `currentColor` | stroke of both diagonals |
| `withOpacity(?float)` | none | opacity of the tile, applied to the whole drawing at once, so the crossings keep the same tone as the lines |
| `withAngle(float)` | `0` | turns the mesh; at `45` the diagonals become a horizontal and a vertical rule |
| `withId(string)` | derived | fixes the identifier instead of deriving it from the tile content |

A thickness above the spacing throws `InvalidArgumentException`.

## How the tile closes

The tile is a square of the spacing, and the two diagonals run corner to corner:
`M 0 0 L s s` and `M s 0 L 0 s`. Every line end sits exactly on a corner of the tile.

The stroke keeps the default butt cap, so the cap of one line meets the cap of the neighbouring
tile's line head on, at the same angle, on the same point. The two read as one continuous
diagonal, and nothing has to be drawn twice.
