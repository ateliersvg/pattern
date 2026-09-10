---
title: Getting started
description: Install Atelier Pattern, fill a shape with a repeating tile, and pick the tile that suits the surface.
order: 10
---

# Getting started

Atelier Pattern builds `<pattern>` tiles. Ask for a tile, put it in the `<defs>` of your
document, and reference it from any `fill` attribute.

## Install

Atelier Pattern requires PHP 8.3 or later, and `atelier/svg`. There is no other runtime
dependency.

```bash
composer require atelier/pattern
```

A tile is an `Atelier\Svg\Element\Gradient\PatternElement`, so the package speaks the
vocabulary of `atelier/svg` and its output goes straight into a document you already build with
it.

## Fill a shape

Create a PHP file that fills a rectangle with dots and writes the result to disk.

```php
<?php

declare(strict_types=1);

use Atelier\Pattern\Pattern;
use Atelier\Pattern\PatternRegistry;
use Atelier\Svg\Document;
use Atelier\Svg\Dumper\CompactXmlDumper;
use Atelier\Svg\Element\Shape\RectElement;

require __DIR__.'/vendor/autoload.php';

$dots = Pattern::dots(spacing: 14, radius: 2.2);

$document = Document::create(400, 240);

(new PatternRegistry($dots))->attachTo($document);

$surface = new RectElement();
$surface->setX('0')->setY('0')->setWidth('400')->setHeight('240');
$surface->setAttribute('fill', $dots->fill());

$document->getRootElement()?->appendChild($surface);

file_put_contents(__DIR__.'/dots.svg', (new CompactXmlDumper())->dump($document));
```

Open `dots.svg` in a browser. The file holds one `<pattern>` in its `<defs>` and one
rectangle that references it.

The example has three stages:

1. `Pattern::dots()` returns a tile. It needs no document and mutates nothing.
2. `attachTo()` writes the tile into the `<defs>` of the document.
3. `$dots->fill()` returns a `url(#...)` reference to that identifier, which a `fill` attribute accepts.

## Give it a colour

A tile paints with `currentColor`, inherited from the definition's ancestors in `<defs>`.
The example above uses the initial colour, black. Set `color` on the SVG root to theme it:

```php
$document->getRootElement()?->setAttribute('color', '#c0392b');
```

Every shape using this definition follows that root colour. Setting `color` on an individual
consuming shape does not recolour the definition. To give shapes independent colours, create
separate patterns with `withColor()` and register each one:

```php
$redDots = $dots->withColor('#c0392b');
$blueDots = $dots->withColor('#0067a0');
(new PatternRegistry($redDots, $blueDots))->attachTo($document);
```

Use `$redDots->fill()` or `$blueDots->fill()` on each shape.

## Choose a tile

A regular tile repeats a single cell. An irregular one encloses its disorder in a larger
tile that still repeats, and takes a `seed`. Identical inputs reproduce the same tiling within
the same generator version and supported runtime behavior. Here are some starting points; the
[complete catalogue](patterns/index.md) includes all twenty-four factories.

| Tile | Start with | Use for |
| --- | --- | --- |
| [dots](patterns/dots.md) | `Pattern::dots()` | An even texture that reads as tone rather than shape, staggered or aligned |
| [stripes](patterns/stripes.md) | `Pattern::stripes()` | Bands, at any angle |
| [crosshatch](patterns/crosshatch.md) | `Pattern::crosshatch()` | Shading that darkens without a solid fill |
| [grid](patterns/grid.md) | `Pattern::grid()` | Ruled cells, for a plan or a background measure |
| [honeycomb](patterns/honeycomb.md) | `Pattern::honeycomb()` | A hexagonal mesh |
| [scales](patterns/scales.md) | `Pattern::scales()` | Overlapping arcs, for water, roofs, or feathers |
| [chevron](patterns/chevron.md) | `Pattern::chevron()` | Stacked zigzags with a direction |
| [checker](patterns/checker.md) | `Pattern::checker()` | Alternating cells, and transparency where they are not filled |
| [truchet](patterns/truchet.md) | `Pattern::truchet()` | Continuous loops with a larger repeat |
| [jitteredDots](patterns/jittered-dots.md) | `Pattern::jitteredDots()` | Dots off their lattice, for a hand-placed texture |
| [roughHatch](patterns/rough-hatch.md) | `Pattern::roughHatch()` | Hatching that reads as drawn rather than ruled |
| [staggeredBricks](patterns/staggered-bricks.md) | `Pattern::staggeredBricks()` | Coursed brickwork with an uneven bond |
| [confetti](patterns/confetti.md) | `Pattern::confetti()` | Scattered shapes of varying size and angle |

Each page shows the tile filling a surface, lists the geometry it accepts, and says how it
closes on itself.

## Style the tile

Factories take geometry. Everything else applies afterwards, and each call returns a new
tile rather than changing the one you had:

```php
Pattern::stripes(spacing: 12, thickness: 4)
    ->withColor('#c0392b')
    ->withOpacity(0.4)
    ->withAngle(45);
```

The angle rides on the tile itself, so it turns the whole pavement. Any angle works, and
none of them needs an oversized tile to cover the corners.

## One definition per tile

The identifier is derived from the tile: its class, its geometry and its style. Two tiles built
with the same values carry the same identifier, so a registry defines them once however many
times they are added. Style takes part in it, which makes two tints of one geometry two
definitions.

```php
Pattern::dots(spacing: 14, radius: 2.2)->id();                      // dots- followed by 32 hexadecimal characters
Pattern::dots(spacing: 14, radius: 2.2)->withId('page-dots')->id();   // page-dots
```

Integer seeds retain all their bits, and floating-point geometry is hashed before SVG coordinates
are rounded. The identifier uses the full 128-bit hash. This replaces the earlier ten-character
suffix, so regenerate definitions and their references together when updating an existing drawing.

Use `fill()` to obtain the matching reference instead of constructing it from an assumed hash
length. `withId()` overrides the derived identifier; assign distinct IDs to distinct patterns,
because the registry keeps the first pattern registered under a given ID.

## Precision and scale

Coordinates serialize to four decimal places. Use ordinary display-sized user units and SVG
scaling for very small or large output. Features below `0.00005` units can round to zero;
very large finite inputs can overflow intermediate geometry calculations. The guards validate
individual parameters, not useful rendering at every accepted scale or a common resource bound.
