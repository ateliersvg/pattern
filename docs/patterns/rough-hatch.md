---
title: Rough hatch
description: Hatching with an uneven spacing and a wandering inclination, for shading that reads as drawn.
order: 130
---

# Rough hatch

Rough hatch draws hatching with an unsteady hand: the gap between two strokes and the
inclination of each segment both wander. Take it when ruled hatching reads too mechanical for
the surface it covers.

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

$roughHatch = Pattern::roughHatch(spacing: 14, thickness: 1.4, jitter: 0.25, cells: 6, seed: 1);

$document = Document::create(360, 200);

(new PatternRegistry($roughHatch))->attachTo($document);

$surface = new RectElement();
$surface->setX('0')->setY('0')->setWidth('360')->setHeight('200');
$surface->setAttribute('fill', $roughHatch->fill());

$document->getRootElement()?->appendChild($surface);

echo (new CompactXmlDumper())->dump($document);
```

## Options

`Pattern::roughHatch()` takes its geometry in user units, plus the size of the repeating tile and
the draw. Style applies afterwards, and each call returns a new tile.

| Setting | Default | Effect |
|---|---|---|
| `spacing` | `14` | distance between two strokes |
| `thickness` | `1.4` | line width, at most the spacing |
| `jitter` | `0.25` | how far a stroke wanders, as a share of the spacing, at most `0.25` |
| `cells` | `6` | strokes and rows of the repeating tile, at least 2; the period is `cells * spacing` |
| `seed` | `1` | where each stroke stands and where it bends |
| `withColor(string)` | `currentColor` | stroke of the hatching |
| `withOpacity(?float)` | none | opacity of the tile, applied to the whole drawing at once, so a stroke overlapping another keeps one flat tone |
| `withAngle(float)` | `0` | turns the hatching; at `90` the strokes run across instead of down |
| `withId(string)` | derived | fixes the identifier instead of deriving it from the tile content |

A thickness above the spacing, a jitter above `0.25`, or a cell count below 2 throws
`InvalidArgumentException`. The jitter ceiling is the tight one: past a quarter of the spacing a
stroke would leave its own column and land in the next.

The same inputs and `seed` draw the same tiling within the same generator version. It takes part in the derived identifier,
so two seeds are two definitions in the `<defs>` rather than one.

## How the tile closes

The repeating tile is a square of `cells` by `cells` cells of one spacing, so the period is
`cells * spacing` on both axes.

A stroke crosses the tile from the top edge to the bottom edge, bending at every row, and the
bends of one stroke sit at their own heights so they do not line up with the bends of the stroke
next to it. Its last node repeats the abscissa of its first, one period down: the two ends share
an abscissa, so the stroke meets the one above and the one below head on. Round caps and round
joins close the junction, and two ends meeting on the seam read as one continuous line.

The first stroke stands on the left edge, and any stroke wandering past a vertical edge is drawn
a second time one period away, against the opposite edge.
