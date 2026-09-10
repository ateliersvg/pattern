---
title: Stripes
description: Parallel bands of a fixed width, vertical by default and turned to any angle through withAngle().
order: 30
---

# Stripes

Stripes draws parallel bands, vertical until you turn them. The band is a filled rectangle
rather than a stroke, so it is exactly as wide as the thickness you asked for.

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

$stripes = Pattern::stripes(spacing: 12, thickness: 4);

$document = Document::create(360, 200);

(new PatternRegistry($stripes))->attachTo($document);

$surface = new RectElement();
$surface->setX('0')->setY('0')->setWidth('360')->setHeight('200');
$surface->setAttribute('fill', $stripes->fill());

$document->getRootElement()?->appendChild($surface);

echo (new CompactXmlDumper())->dump($document);
```

## Options

`Pattern::stripes()` takes two measurements in user units. Style applies afterwards, and each
call returns a new tile.

| Setting | Default | Effect |
|---|---|---|
| `spacing` | `12` | distance between the left edges of two bands, and the side of the tile |
| `thickness` | `4` | band width, at most the spacing; the gap is what is left |
| `angle` | `0` | turns the tile at construction; the same result as `withAngle()`, given where the geometry is chosen |
| `withColor(string)` | `currentColor` | paint of the band; the gap carries no ground of its own |
| `withOpacity(?float)` | none | opacity of the tile; `null` leaves the attribute out |
| `withAngle(float)` | `0` | turns the bands; `45` gives diagonals, `90` gives horizontals |
| `withId(string)` | derived | fixes the identifier instead of deriving it from the tile content |

A thickness above the spacing throws `InvalidArgumentException`: a band wider than its period
would cover the gap.

## How the tile closes

The tile is a square of the spacing, and the band stands on its left edge: `thickness` wide and
the full height of the tile. Below `spacing`, the thickness leaves a transparent gap on the
right. At `thickness: spacing`, the band reaches that edge and fills the tile completely.
Nothing crosses a boundary and nothing is drawn twice.

The height carries no information. The band spans it, and the pattern repeats down the surface
without changing.

An angle changes none of this. It rides on `patternTransform`, which turns the pavement after
the tile is laid, so a diagonal stripe needs no oversized tile and leaves no clipped corner.
