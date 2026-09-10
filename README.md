<h1 align="center">Atelier Pattern</h1>

<p align="center">Repeatable SVG tiles for fills and strokes, in PHP.</p>

<p align="center">
  <img alt="PHP Version" src="https://img.shields.io/badge/PHP-8.3%2B-f4715c?labelColor=14141c">
  <img alt="PHPUnit" src="https://img.shields.io/badge/PHPUnit-12-f4715c?labelColor=14141c">
  <img alt="PHPStan" src="https://img.shields.io/badge/PHPStan-max-f4715c?labelColor=14141c">
  <a href="LICENSE"><img alt="License" src="https://img.shields.io/badge/License-MIT-f4715c?labelColor=14141c"></a>
</p>

Fill a surface with dots, stripes, grids, or geometric and seeded motifs. Each tile joins
with itself so the pattern repeats across the surface.

```php
use Atelier\Pattern\Pattern;

$dots = Pattern::dots(spacing: 14, radius: 2.2, stagger: 0.5)->withColor('#f4715c');
```

<p align="center">
  <img src="docs/images/dots.svg" width="240" alt="Staggered dots repeating across a surface">
  <img src="docs/images/seigaiha.svg" width="240" alt="Overlapping arcs forming a repeating seigaiha pattern">
</p>

A pattern is an immutable value. It needs no document to exist and provides a `<pattern>`
element plus the `url(#id)` that references it. Atelier SVG supplies the document and element
vocabulary. Drawings generated for one viewport belong to the companion `atelier/field` package.

**[Style](#style) · [Catalogue](#catalogue) · [Errors](#error-handling) · [Gallery](#gallery) · [Documentation](#documentation)**

## Installation

```bash
composer require atelier/pattern
```

Requires PHP 8.3+ and `atelier/svg`. Composer installs the package dependencies.

## Quick start

```php
<?php

declare(strict_types=1);

use Atelier\Pattern\Pattern;
use Atelier\Pattern\PatternRegistry;
use Atelier\Svg\Document;
use Atelier\Svg\Dumper\CompactXmlDumper;
use Atelier\Svg\Element\Shape\RectElement;

require __DIR__.'/vendor/autoload.php';

$dots = Pattern::dots(spacing: 14, radius: 2.2)->withColor('#f4715c')->withOpacity(0.4);

$document = Document::create(600, 400);
(new PatternRegistry($dots))->attachTo($document);

$surface = new RectElement();
$surface->setX('0')->setY('0')->setWidth('600')->setHeight('400');
$surface->setAttribute('fill', $dots->fill());

$document->getRootElement()?->appendChild($surface);

echo (new CompactXmlDumper())->dump($document);
```

The tile lands in `<defs>` under an identifier derived from its content, and `fill()` returns
a `url(#...)` reference to that same identifier.

## Style

Factories take geometry. Style applies afterwards and returns a new pattern each time.
The following snippets reuse the imports and autoloader above.

```php
Pattern::stripes(spacing: 12, thickness: 4)
    ->withColor('#c0392b')
    ->withOpacity(0.4)
    ->withAngle(30);
```

The default paint is `currentColor` and the tile background is transparent. The colour inherits
from the pattern definition's ancestors in `<defs>`; setting `color` on the SVG root themes it.
A consuming shape's own `color` does not recolour that definition. For independent colours,
create patterns with different `withColor()` values and register each one.

## Catalogue

<table>
  <tr>
    <td align="center" width="50%">
      <a href="docs/patterns/dots.md"><img src="docs/images/dots.svg" width="180" alt="An even lattice of dots"><br>Dots</a>
    </td>
    <td align="center" width="50%">
      <a href="docs/patterns/stripes.md"><img src="docs/images/stripes.svg" width="180" alt="Parallel bands"><br>Stripes</a>
    </td>
  </tr>
  <tr>
    <td align="center" width="50%">
      <a href="docs/patterns/crosshatch.md"><img src="docs/images/crosshatch.svg" width="180" alt="Two sets of diagonals crossing"><br>Crosshatch</a>
    </td>
    <td align="center" width="50%">
      <a href="docs/patterns/grid.md"><img src="docs/images/grid.svg" width="180" alt="Square cells drawn with rules"><br>Grid</a>
    </td>
  </tr>
  <tr>
    <td align="center" width="50%">
      <a href="docs/patterns/triangles.md"><img src="docs/images/triangles.svg" width="180" alt="A tessellation of equilateral triangles"><br>Triangles</a>
    </td>
    <td align="center" width="50%">
      <a href="docs/patterns/honeycomb.md"><img src="docs/images/honeycomb.svg" width="180" alt="A hexagonal mesh"><br>Honeycomb</a>
    </td>
  </tr>
  <tr>
    <td align="center" width="50%">
      <a href="docs/patterns/scales.md"><img src="docs/images/scales.svg" width="180" alt="Overlapping arcs laid out like fish scales"><br>Scales</a>
    </td>
    <td align="center" width="50%">
      <a href="docs/patterns/herringbone.md"><img src="docs/images/herringbone.svg" width="180" alt="Bricks laid in a herringbone"><br>Herringbone</a>
    </td>
  </tr>
  <tr>
    <td align="center" width="50%">
      <a href="docs/patterns/chevron.md"><img src="docs/images/chevron.svg" width="180" alt="Stacked zigzags"><br>Chevron</a>
    </td>
    <td align="center" width="50%">
      <a href="docs/patterns/houndstooth.md"><img src="docs/images/houndstooth.svg" width="180" alt="Houndstooth check"><br>Houndstooth</a>
    </td>
  </tr>
  <tr>
    <td align="center" width="50%">
      <a href="docs/patterns/isometric-cubes.md"><img src="docs/images/isometric-cubes.svg" width="180" alt="Rhombs shaded as a stack of cubes"><br>Isometric cubes</a>
    </td>
    <td align="center" width="50%">
      <a href="docs/patterns/asanoha.md"><img src="docs/images/asanoha.svg" width="180" alt="Asanoha, the hemp leaf"><br>Asanoha</a>
    </td>
  </tr>
  <tr>
    <td align="center" width="50%">
      <a href="docs/patterns/seigaiha.md"><img src="docs/images/seigaiha.svg" width="180" alt="Rows of concentric fans"><br>Seigaiha</a>
    </td>
    <td align="center" width="50%">
      <a href="docs/patterns/flower-of-life.md"><img src="docs/images/flower-of-life.svg" width="180" alt="Circles on a triangular lattice"><br>Flower of life</a>
    </td>
  </tr>
  <tr>
    <td align="center" width="50%">
      <a href="docs/patterns/checker.md"><img src="docs/images/checker.svg" width="180" alt="Alternating filled and transparent cells"><br>Checker</a>
    </td>
    <td align="center" width="50%">
      <a href="docs/patterns/quatrefoil.md"><img src="docs/images/quatrefoil.svg" width="180" alt="Four lobes to a rosette"><br>Quatrefoil</a>
    </td>
  </tr>
  <tr>
    <td align="center" width="50%">
      <a href="docs/patterns/greek-key.md"><img src="docs/images/greek-key.svg" width="180" alt="A running meander"><br>Greek key</a>
    </td>
    <td align="center" width="50%">
      <a href="docs/patterns/truchet.md"><img src="docs/images/truchet.svg" width="180" alt="Quarter circle arcs forming continuous loops"><br>Truchet</a>
    </td>
  </tr>
  <tr>
    <td align="center" width="50%">
      <a href="docs/patterns/jittered-dots.md"><img src="docs/images/jittered-dots.svg" width="180" alt="Dots scattered off their lattice"><br>Jittered dots</a>
    </td>
    <td align="center" width="50%">
      <a href="docs/patterns/rough-hatch.md"><img src="docs/images/rough-hatch.svg" width="180" alt="Hatching with uneven spacing and slope"><br>Rough hatch</a>
    </td>
  </tr>
  <tr>
    <td align="center" width="50%">
      <a href="docs/patterns/staggered-bricks.md"><img src="docs/images/staggered-bricks.svg" width="180" alt="Coursed brickwork with an uneven bond"><br>Staggered bricks</a>
    </td>
    <td align="center" width="50%">
      <a href="docs/patterns/confetti.md"><img src="docs/images/confetti.svg" width="180" alt="Scattered shapes of varying size and angle"><br>Confetti</a>
    </td>
  </tr>
  <tr>
    <td align="center" width="50%">
      <a href="docs/patterns/voronoi.md"><img src="docs/images/voronoi.svg" width="180" alt="The edges of a Voronoi partition"><br>Voronoi</a>
    </td>
    <td align="center" width="50%">
      <a href="docs/patterns/mosaic.md"><img src="docs/images/mosaic.svg" width="180" alt="A Voronoi partition read on a grid of square cells"><br>Mosaic</a>
    </td>
  </tr>
</table>

## Error handling

Invalid geometry or styling throws `Atelier\Pattern\Exception\InvalidArgumentException`.
All package exceptions implement `Atelier\Pattern\Exception\ExceptionInterface`.
Each tile documents its accepted ranges; see [Getting started](docs/getting-started.md)
for validation, identifiers, and seeded output guarantees.

## Gallery

```bash
composer gallery
```

Writes `examples/output/index.html` with every tile rendered at 300 by 200.

## Documentation

- [Getting started](docs/getting-started.md): install the package and produce a first SVG.
- [Illustrated catalogue](docs/patterns/index.md): choose a tile and explore its parameters.
- [Package overview](docs/index.md): understand the API and its boundaries.

Read the complete guides and generated illustrations in [docs/](docs/).

## Contributing

Contributions are welcome. Visit the [project on GitHub](https://github.com/ateliersvg/pattern)
to [report a bug](https://github.com/ateliersvg/pattern/issues/new),
[suggest a feature](https://github.com/ateliersvg/pattern/issues/new), or
[open a pull request](https://github.com/ateliersvg/pattern/pulls).

Before submitting code, run:

```bash
composer qa
composer coverage
```

Changes to public behaviour need tests and a documentation update.
Keep line coverage at 100%. Coverage reporting requires PCOV or Xdebug.

## Support

Bug reports, security disclosures, and contribution guidelines are collected at
[ateliersvg.com/support](https://ateliersvg.com/support/).

## License

Atelier Pattern is released under the [MIT License](LICENSE).
