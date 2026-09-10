<?php

declare(strict_types=1);

use Atelier\Pattern\AbstractPattern;
use Atelier\Pattern\Pattern;
use Atelier\Pattern\PatternRegistry;
use Atelier\Svg\Document;
use Atelier\Svg\Dumper\CompactXmlDumper;
use Atelier\Svg\Element\Shape\RectElement;

require_once __DIR__.'/../vendor/autoload.php';

/** @var list<array{string, AbstractPattern, string}> $items */
$items = [
    ['dots', Pattern::dots(spacing: 14, radius: 2.4), 'One dot per cell, centred.'],
    ['dots, stagger 0.5', Pattern::dots(spacing: 15, radius: 2.4, stagger: 0.5), 'Every other row shifted by half a spacing.'],
    ['dots, phase 0.5', Pattern::dots(spacing: 18, radius: 1.8, phase: 0.5), 'The same lattice on the nodes of the grid.'],
    ['stripes', Pattern::stripes(spacing: 13, thickness: 5), 'Parallel bands.'],
    ['stripes, angle 45', Pattern::stripes(spacing: 13, thickness: 5, angle: 45), 'The same tile, turned.'],
    ['crosshatch', Pattern::crosshatch(spacing: 14, thickness: 1.2), 'Two diagonals crossing.'],
    ['grid', Pattern::grid(size: 18, thickness: 1), 'Square cells drawn with rules.'],
    ['honeycomb', Pattern::honeycomb(radius: 14, thickness: 1.4), 'Hexagons standing on a point.'],
    ['scales', Pattern::scales(width: 22, height: 14, thickness: 1.4), 'Arcs laid out like fish scales.'],
    ['chevron', Pattern::chevron(size: 24, thickness: 2), 'Stacked zigzags.'],
    ['checker', Pattern::checker(size: 14), 'Filled cells on a diagonal.'],
    ['truchet', Pattern::truchet(tile: 22, thickness: 2.2, cells: 8, seed: 1), 'Quarter circles turned at random.'],
    ['jitteredDots', Pattern::jitteredDots(spacing: 15, radius: 2.4, jitter: 0.3, cells: 6, seed: 1), 'A lattice of dots pushed off their nodes.'],
    ['roughHatch', Pattern::roughHatch(spacing: 13, thickness: 1.4, jitter: 0.25, cells: 6, seed: 1), 'Hatching with an unsteady hand.'],
    ['staggeredBricks', Pattern::staggeredBricks(width: 30, height: 13, thickness: 1.2, jitter: 0.2, cells: 4, seed: 1), 'A bond drawn rather than measured.'],
    ['confetti', Pattern::confetti(spacing: 18, size: 4, cells: 5, seed: 1), 'Small shapes thrown across the surface.'],
    ['confetti, seed 7', Pattern::confetti(spacing: 18, size: 4, cells: 5, seed: 7), 'Another seed, another tile.'],
    ['voronoi', Pattern::voronoi(size: 150, thickness: 1.5, sites: 16, seed: 1), 'A Voronoi diagram computed on a torus.'],
    ['voronoi, seed 7', Pattern::voronoi(size: 150, thickness: 1.5, sites: 16, seed: 7), 'Another seed, another set of cells.'],
    ['voronoi, filled', Pattern::voronoi(size: 150, thickness: 1.5, sites: 16, seed: 3, filled: true), 'Each cell at an opacity of its own.'],
    ['mosaic', Pattern::mosaic(size: 150, cells: 24, sites: 14, tones: 7, seed: 1), 'The same territories read on a grid.'],
    ['houndstooth', Pattern::houndstooth(size: 30), 'The broken check of the pied-de-poule.'],
    ['isometricCubes', Pattern::isometricCubes(size: 24), 'Cubes stacked in isometric projection.'],
    ['asanoha', Pattern::asanoha(size: 26, thickness: 1.2), 'The Japanese hemp leaf.'],
    ['triangles', Pattern::triangles(size: 20), 'The third regular tessellation.'],
    ['herringbone', Pattern::herringbone(length: 16, gap: 1.2), 'Every brick turned against the last.'],
    ['seigaiha', Pattern::seigaiha(radius: 20, rise: 9), 'The blue ocean wave.'],
    ['flowerOfLife', Pattern::flowerOfLife(radius: 18), 'Circles through one another\'s middles.'],
    ['quatrefoil', Pattern::quatrefoil(size: 26, lobe: 5), 'Four lobes to a rosette.'],
    ['greekKey', Pattern::greekKey(size: 36), 'The fret, drawn as one line.'],
    ['dots, withOpacity(0.35)', Pattern::dots(spacing: 12, radius: 4)->withOpacity(0.35), 'Opacity carried by the tile.'],
];

$swatch = static function (AbstractPattern $pattern): string {
    $width = 300;
    $height = 200;

    $document = Document::create($width, $height);
    $root = $document->getRootElement();

    if (null === $root) {
        throw new RuntimeException('The document has no root element.');
    }

    $root->setAttribute('viewBox', sprintf('0 0 %d %d', $width, $height));
    $root->removeAttribute('width');
    $root->removeAttribute('height');

    (new PatternRegistry($pattern))->attachTo($document);

    $surface = new RectElement();
    $surface->setX('0')->setY('0')->setWidth((string) $width)->setHeight((string) $height);
    $surface->setAttribute('fill', $pattern->fill());
    $root->appendChild($surface);

    $dumper = new CompactXmlDumper();
    $dumper->includeXmlDeclaration(false);

    return $dumper->dump($document);
};

$cards = '';

foreach ($items as [$title, $pattern, $description]) {
    $cards .= '<article>'
        .'<div class="swatch">'.$swatch($pattern).'</div>'
        .'<h2>'.htmlspecialchars($title, ENT_QUOTES).'</h2>'
        .'<p>'.htmlspecialchars($description, ENT_QUOTES).'</p>'
        .'<code>'.htmlspecialchars($pattern->id(), ENT_QUOTES).'</code>'
        .'</article>';
}

$html = <<<HTML
<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>atelier/pattern</title>
<style>
*,*::before,*::after{box-sizing:border-box}
body{margin:0;background:#fbfbf9;color:#16202c;font-family:ui-sans-serif,system-ui,-apple-system,"Segoe UI",sans-serif;line-height:1.5}
main{width:min(1120px,calc(100% - 48px));margin:0 auto;padding:64px 0}
h1{margin:0 0 8px;font-size:32px;font-weight:600;letter-spacing:-0.01em}
.lede{margin:0 0 48px;max-width:52ch;color:#59616b}
.grid{display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:32px 24px}
article{margin:0}
.swatch{border:1px solid #d9dbd6;background:#ffffff;color:#16202c}
svg{display:block;width:100%;aspect-ratio:3/2}
h2{margin:12px 0 0;font-size:15px;font-weight:600;font-family:ui-monospace,SFMono-Regular,Menlo,monospace}
p{margin:2px 0 0;color:#59616b;font-size:14px}
code{display:block;margin-top:6px;color:#8b9099;font-size:12px;font-family:ui-monospace,SFMono-Regular,Menlo,monospace}
@media(max-width:860px){.grid{grid-template-columns:repeat(2,minmax(0,1fr))}}
@media(max-width:560px){.grid{grid-template-columns:1fr}}
</style>
</head>
<body>
<main>
  <h1>atelier/pattern</h1>
  <p class="lede">Repeatable SVG tiles. Every tile joins with itself: nothing is cut at an edge, and no seam runs through the surface. Colour comes from the host through currentColor.</p>
  <section class="grid">{$cards}</section>
</main>
</body>
</html>
HTML;

if (!is_dir(__DIR__.'/output') && !mkdir(__DIR__.'/output', 0o777, true) && !is_dir(__DIR__.'/output')) {
    throw new RuntimeException('Cannot create the output directory.');
}

file_put_contents(__DIR__.'/output/index.html', $html);

echo __DIR__."/output/index.html\n";
