---
title: Jittered dots
description: A dotted lattice with every dot pushed off its node, for a texture that reads as placed by hand.
order: 120
---

# Jittered dots

Jittered dots pushes every dot off its node by an amount drawn at random on each axis. Take it
when a dotted texture should look placed rather than measured.

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

$jitteredDots = Pattern::jitteredDots(spacing: 16, radius: 2.4, jitter: 0.5, cells: 6, seed: 1);

$document = Document::create(360, 200);

(new PatternRegistry($jitteredDots))->attachTo($document);

$surface = new RectElement();
$surface->setX('0')->setY('0')->setWidth('360')->setHeight('200');
$surface->setAttribute('fill', $jitteredDots->fill());

$document->getRootElement()?->appendChild($surface);

echo (new CompactXmlDumper())->dump($document);
```

## Options

`Pattern::jitteredDots()` takes its geometry in user units, plus the size of the repeating tile
and the draw. Style applies afterwards, and each call returns a new tile.

| Setting | Default | Effect |
|---|---|---|
| `spacing` | `16` | distance between two lattice nodes |
| `radius` | `2.4` | dot radius, at most half the spacing |
| `jitter` | `0.5` | how far a dot leaves its node on each axis, as a share of the spacing, at most `1` |
| `cells` | `6` | cells per side of the repeating tile, at least 2; the period is `cells * spacing` |
| `seed` | `1` | where each dot lands inside its reach |
| `withColor(string)` | `currentColor` | paint of the dots |
| `withOpacity(?float)` | none | opacity of the tile, applied to the whole drawing at once, so two dots that touch keep one flat tone |
| `withAngle(float)` | `0` | turns the whole scatter through `patternTransform` |
| `withId(string)` | derived | fixes the identifier instead of deriving it from the tile content |

A radius above half the spacing, a jitter above `1`, or a cell count below 2 throws
`InvalidArgumentException`.

The same inputs and `seed` draw the same tiling within the same generator version. It takes part in the derived identifier,
so two seeds are two definitions in the `<defs>` rather than one.

## How the tile closes

The repeating tile is a square of `cells` by `cells` cells of one spacing, so the period is
`cells * spacing` on both axes and the disorder stops at its border.

The nodes sit on the corners of the cells, so the first row and the first column land on the
edges of the tile. A dot pushed off one of those overhangs, and it is drawn a second time one
period away, against the opposite edge; a dot straddling a corner is drawn on all four. The clip
then takes nothing away, and the pieces rebuild one dot across the seam.

The two axes are drawn apart, each within `jitter` times the spacing, so a dot lands anywhere in
the square its jitter allows rather than piling up near its node. At the default of `0.5` that
square is the cell itself and the lattice stops showing through. Below it the rows still read;
above it the dots of two cells mix, which is where the ceiling of `1` comes from: past it a dot
reaches a neighbouring node and the lattice is gone.
