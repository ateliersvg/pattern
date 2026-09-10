---
title: Seigaiha
description: The blue ocean wave, rows of concentric fans where each row takes most of the one behind it.
order: 98
---

# Seigaiha

Seigaiha draws rows of concentric fans, each row standing half a fan across from the one behind it
and hiding most of it. Take it for water, a textile ground, or any surface that should carry a
motif rather than a texture.

It is the wave of Japanese ceramic and cloth, and on paper the fan in front is opaque and covers
what is behind it. A tile paints no ground, so there is nothing here to cover with: each arc is
cut instead, and what is left is the scallop.

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

$seigaiha = Pattern::seigaiha(radius: 26, rise: 12, rings: 4);

$document = Document::create(360, 200);

(new PatternRegistry($seigaiha))->attachTo($document);

$surface = new RectElement();
$surface->setX('0')->setY('0')->setWidth('360')->setHeight('200');
$surface->setAttribute('fill', $seigaiha->fill());

$document->getRootElement()?->appendChild($surface);

echo (new CompactXmlDumper())->dump($document);
```

## Options

| Setting | Default | Effect |
|---|---|---|
| `radius` | `26` | radius of the outer arc; the tile is two of them wide |
| `rise` | `12` | distance between two rows, at most half the radius |
| `rings` | `4` | arcs to a fan, 2 to 8; they step in by an equal share of the radius |
| `thickness` | `1.1` | line width, at most a tenth of the radius |
| `withColor(string)` | `currentColor` | paint of the arcs |
| `withOpacity(?float)` | none | opacity of the whole tile |
| `withAngle(float)` | `0` | turns the rows through `patternTransform` |
| `withId(string)` | derived | fixes the identifier instead of deriving it from the tile content |

The `rise` is the pattern. A small one leaves a shallow scallop of every fan and the surface reads
as scales. At the allowed maximum, `radius / 2`, rows are farther apart and more of each fan
is exposed, while neighbouring rows still overlap.

Fans touch along a row rather than overlapping, so a fan is only ever cut by the rows in front of
it. For each of those, the angular span the arc spends inside the fan is found from the law of
cosines on the two circles, and the spans that survive are what gets drawn. Painting each fan with
a ground and letting the painter's order hide the rest would make the tile opaque: a fill would
have to be chosen, and the surface underneath would stop showing through.

## How the tile closes

The tile is two radii wide and two rises tall, and holds one fan per row: one at its corner and
one half a tile across and one rise down.

An arc reaching past an edge is drawn again a period away, and the arcs that reach past two edges
at once are drawn at the corner as well.
