---
title: Staggered bricks
description: Coursed brickwork whose bond is dealt rather than measured, so no two courses read the same.
order: 140
---

# Staggered bricks

Staggered bricks draws the joints of a brick wall whose bond is dealt rather than measured: each
course lands within `jitter` of the running bond mark instead of exactly on it. Take it for
masonry, or for any coursed surface that should not look machined.

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

$bricks = Pattern::staggeredBricks(width: 28, height: 12, thickness: 1.2, jitter: 0.2, cells: 4, seed: 1);

$document = Document::create(360, 200);

(new PatternRegistry($bricks))->attachTo($document);

$surface = new RectElement();
$surface->setX('0')->setY('0')->setWidth('360')->setHeight('200');
$surface->setAttribute('fill', $bricks->fill());

$document->getRootElement()?->appendChild($surface);

echo (new CompactXmlDumper())->dump($document);
```

## Options

`Pattern::staggeredBricks()` takes its geometry in user units, plus the height of the repeating
tile and the draw. Style applies afterwards, and each call returns a new tile.

| Setting | Default | Effect |
|---|---|---|
| `width` | `28` | length of one brick, and the width of the tile |
| `height` | `12` | height of one course |
| `thickness` | `1.2` | joint width, at most the brick length and at most the course height |
| `jitter` | `0.2` | how far a course leaves the bond mark, as a share of the brick |
| `cells` | `4` | courses of the repeating tile, at least 2; the period is `cells * height` down |
| `seed` | `1` | where each head joint lands inside its reach |
| `withColor(string)` | `currentColor` | paint of the joints; the bricks themselves are left transparent |
| `withOpacity(?float)` | none | opacity of the tile, applied to the whole drawing at once, so a head joint meeting a bed joint keeps one flat tone |
| `withAngle(float)` | `0` | turns the courses through `patternTransform` |
| `withId(string)` | derived | fixes the identifier instead of deriving it from the tile content |

The `jitter` ceiling is computed from the other arguments rather than fixed: two courses each
wobble by up to the jitter, so their offsets move apart by up to twice it, and past that a head
joint lands on the one below, which is the straight crack a bond exists to avoid. The bound
therefore depends on the bond step, the joint width, and the course count. Exceeding it throws
`InvalidArgumentException`, and so does a course count below 2.

The same inputs and `seed` draw the same tiling within the same generator version. It takes part in the derived identifier,
so two seeds are two definitions in the `<defs>` rather than one.

## How the tile closes

The tile is one brick wide and `cells` courses tall. A course is one bed joint running the full
width and one head joint standing between two bricks.

The bond has to come back where it started after those courses, or the seam between two tiles
shows a straight crack down the wall. It steps by `intdiv(cells, 2) / cells` bricks per course,
which closes for any count and is exactly half a brick, the mason's answer, whenever the count
is even.

The bed joint spans the tile edge to edge and needs nothing. A head joint straddling the
vertical edge is drawn a second time one period away, against the opposite edge.
