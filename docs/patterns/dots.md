---
title: Dots
description: Aligned or staggered rows of round dots, with phase control and wrapped edges.
order: 10
---

# Dots

Dots draws an aligned lattice by default; `stagger` shifts alternate rows and `phase` moves
the lattice relative to the tile origin. Take it when a surface
needs tone rather than a shape: at a small radius it reads as a tint, at a large one as a
texture.

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

$dots = Pattern::dots(spacing: 14, radius: 2.2, stagger: 0.5);

$document = Document::create(360, 200);

(new PatternRegistry($dots))->attachTo($document);

$surface = new RectElement();
$surface->setX('0')->setY('0')->setWidth('360')->setHeight('200');
$surface->setAttribute('fill', $dots->fill());

$document->getRootElement()?->appendChild($surface);

echo (new CompactXmlDumper())->dump($document);
```

## Options

`Pattern::dots()` takes two measurements in user units. Style applies afterwards, and each call
returns a new tile.

| Setting | Default | Effect |
|---|---|---|
| `spacing` | `14` | horizontal dot spacing and distance between rows |
| `radius` | `2.2` | dot radius, at most half the spacing |
| `stagger` | `0` | shifts every other row by this fraction in `[0,1)` of a spacing; `0` is an aligned lattice, `0.5` a staggered one |
| `phase` | `0` | moves the whole lattice by this fraction in `[0,1)` of a spacing, so a dot can sit on the node of a grid rather than the centre of a cell |
| `withColor(string)` | `currentColor` | paint of the dot; the tile carries no ground of its own |
| `withOpacity(?float)` | none | opacity of the tile; `null` leaves the attribute out |
| `withAngle(float)` | `0` | turns the lattice through `patternTransform` |
| `withId(string)` | derived | fixes the identifier instead of deriving it from the tile content |

A radius above half the spacing throws `Atelier\Pattern\Exception\InvalidArgumentException`.
That is the geometry limit supported by this implementation.

## How the tile closes

With `stagger: 0`, the tile is a square of side `spacing`. With a nonzero stagger, it is
`spacing` wide and `2 * spacing` tall, holding both row offsets before it repeats.

At the default phase, the aligned tile needs one centred circle. A phase shift or stagger can
move a circle across an edge. The tile includes its translated copy against the opposite edge,
and a corner copy when it crosses both axes, so each clipped piece continues in the next tile.
