<?php

declare(strict_types=1);

namespace Atelier\Pattern;

use Atelier\Pattern\Tile\Asanoha;
use Atelier\Pattern\Tile\Checker;
use Atelier\Pattern\Tile\Chevron;
use Atelier\Pattern\Tile\Confetti;
use Atelier\Pattern\Tile\Crosshatch;
use Atelier\Pattern\Tile\Dots;
use Atelier\Pattern\Tile\FlowerOfLife;
use Atelier\Pattern\Tile\GreekKey;
use Atelier\Pattern\Tile\Grid;
use Atelier\Pattern\Tile\Herringbone;
use Atelier\Pattern\Tile\Honeycomb;
use Atelier\Pattern\Tile\Houndstooth;
use Atelier\Pattern\Tile\IsometricCubes;
use Atelier\Pattern\Tile\JitteredDots;
use Atelier\Pattern\Tile\Mosaic;
use Atelier\Pattern\Tile\Quatrefoil;
use Atelier\Pattern\Tile\RoughHatch;
use Atelier\Pattern\Tile\Scales;
use Atelier\Pattern\Tile\Seigaiha;
use Atelier\Pattern\Tile\StaggeredBricks;
use Atelier\Pattern\Tile\Stripes;
use Atelier\Pattern\Tile\Triangles;
use Atelier\Pattern\Tile\Truchet;
use Atelier\Pattern\Tile\Voronoi;

/**
 * Entry point to the catalogue.
 *
 * Every factory takes geometry only. Style comes after, through withColor(),
 * withOpacity() and withAngle().
 *
 * The irregular tiles take a seed on top of their geometry. The same seed and
 * the same version give the same drawing, and two seeds are two tiles: the seed
 * is part of the identifier.
 */
final class Pattern
{
    /**
     * A lattice of dots. A stagger of 0.5 shifts every other row by half a
     * spacing; a phase of 0.5 moves the dots from the middle of the cells onto
     * the nodes of the grid they draw.
     */
    public static function dots(float $spacing = 14.0, float $radius = 2.2, float $stagger = 0.0, float $phase = 0.0): Dots
    {
        return Dots::create($spacing, $radius, $stagger, $phase);
    }

    /**
     * Parallel bands, vertical unless given an angle.
     */
    public static function stripes(float $spacing = 12.0, float $thickness = 4.0, float $angle = 0.0): Stripes
    {
        return Stripes::create($spacing, $thickness, $angle);
    }

    /**
     * Two diagonals crossing.
     */
    public static function crosshatch(float $spacing = 12.0, float $thickness = 1.2, float $angle = 0.0): Crosshatch
    {
        return Crosshatch::create($spacing, $thickness, $angle);
    }

    /**
     * Square cells drawn with rules.
     */
    public static function grid(float $size = 18.0, float $thickness = 1.0): Grid
    {
        return Grid::create($size, $thickness);
    }

    /**
     * A hexagonal mesh.
     */
    public static function honeycomb(float $radius = 12.0, float $thickness = 1.1): Honeycomb
    {
        return Honeycomb::create($radius, $thickness);
    }

    /**
     * Overlapping arcs laid out like fish scales.
     */
    public static function scales(float $width = 18.0, float $height = 12.0, float $thickness = 1.0): Scales
    {
        return Scales::create($width, $height, $thickness);
    }

    /**
     * Stacked zigzags.
     */
    public static function chevron(float $size = 20.0, float $thickness = 2.0): Chevron
    {
        return Chevron::create($size, $thickness);
    }

    /**
     * A checkerboard of filled and transparent cells.
     */
    public static function checker(float $size = 12.0): Checker
    {
        return Checker::create($size);
    }

    /**
     * Quarter circles turned at random, reading as one tangle of curves.
     */
    public static function truchet(float $tile = 24.0, float $thickness = 2.5, int $cells = 8, int $seed = 1): Truchet
    {
        return Truchet::create($tile, $thickness, $cells, $seed);
    }

    /**
     * A lattice of dots, each pushed off its node.
     */
    public static function jitteredDots(float $spacing = 16.0, float $radius = 2.4, float $jitter = 0.5, int $cells = 6, int $seed = 1): JitteredDots
    {
        return JitteredDots::create($spacing, $radius, $jitter, $cells, $seed);
    }

    /**
     * Hatching with an uneven spacing and a wandering inclination.
     */
    public static function roughHatch(float $spacing = 14.0, float $thickness = 1.4, float $jitter = 0.25, int $cells = 6, int $seed = 1): RoughHatch
    {
        return RoughHatch::create($spacing, $thickness, $jitter, $cells, $seed);
    }

    /**
     * Brickwork whose bond is drawn at random rather than measured.
     */
    public static function staggeredBricks(float $width = 28.0, float $height = 12.0, float $thickness = 1.2, float $jitter = 0.2, int $cells = 4, int $seed = 1): StaggeredBricks
    {
        return StaggeredBricks::create($width, $height, $thickness, $jitter, $cells, $seed);
    }

    /**
     * Small shapes thrown across the surface, each with its own size and bearing.
     */
    public static function confetti(float $spacing = 20.0, float $size = 4.0, int $cells = 5, int $seed = 1): Confetti
    {
        return Confetti::create($spacing, $size, $cells, $seed);
    }

    /**
     * The cells of a Voronoi diagram, computed on a torus so they run on across
     * the seam. Filled, each cell takes an opacity of its own between the two
     * bounds.
     */
    public static function voronoi(float $size = 120.0, float $thickness = 1.4, int $sites = 14, int $seed = 1, bool $filled = false, float $minOpacity = 0.15, float $maxOpacity = 0.65): Voronoi
    {
        return Voronoi::create($size, $thickness, $sites, $seed, $filled, $minOpacity, $maxOpacity);
    }

    /**
     * A Voronoi diagram read on a grid: each square takes the tone of the seed
     * nearest its middle, the tone coming from the seed index modulo tones. The
     * distance is measured on the torus, so the territories run on across the
     * seam.
     */
    public static function mosaic(float $size = 150.0, int $cells = 24, int $sites = 14, int $tones = 7, int $seed = 1, bool $grid = true, bool $seeds = true): Mosaic
    {
        return Mosaic::create($size, $cells, $sites, $tones, $seed, $grid, $seeds);
    }

    /**
     * The broken check of the woven pied-de-poule.
     */
    public static function houndstooth(float $size = 24.0, float $thickness = 0.0): Houndstooth
    {
        return Houndstooth::create($size, $thickness);
    }

    /**
     * Cubes stacked in isometric projection, three tones of one ink.
     */
    public static function isometricCubes(float $size = 22.0, float $topFace = 0.3, float $leftFace = 0.85, float $rightFace = 0.55): IsometricCubes
    {
        return IsometricCubes::create($size, $topFace, $leftFace, $rightFace);
    }

    /**
     * Asanoha, the Japanese hemp leaf, on a hexagonal lattice.
     */
    public static function asanoha(float $size = 28.0, float $thickness = 1.2): Asanoha
    {
        return Asanoha::create($size, $thickness);
    }

    /**
     * The triangular tessellation, one ink for each orientation. Equal values
     * give a plain lattice of triangles.
     */
    public static function triangles(float $size = 24.0, float $up = 1.0, float $down = 0.3): Triangles
    {
        return Triangles::create($size, $up, $down);
    }

    /**
     * Bricks laid in a herringbone, each one turned against the one before it.
     */
    public static function herringbone(float $length = 24.0, float $gap = 1.6): Herringbone
    {
        return Herringbone::create($length, $gap);
    }

    /**
     * Four lobes to a rosette, laid on a lattice that carries a second rosette
     * in the middle of every cell.
     */
    public static function quatrefoil(float $size = 34.0, float $lobe = 6.4): Quatrefoil
    {
        return Quatrefoil::create($size, $lobe);
    }

    /**
     * Seigaiha, the blue ocean wave: rows of fans, each one cut by the fans
     * standing in front of it.
     */
    public static function seigaiha(float $radius = 26.0, float $rise = 12.0, int $rings = 4, float $thickness = 1.1): Seigaiha
    {
        return Seigaiha::create($radius, $rise, $rings, $thickness);
    }

    /**
     * The Greek fret: a key spiralling off a continuous rule.
     */
    public static function greekKey(float $size = 40.0, float $thickness = 1.4): GreekKey
    {
        return GreekKey::create($size, $thickness);
    }

    /**
     * The flower of life: circles spaced by their own radius on a triangular
     * lattice.
     */
    public static function flowerOfLife(float $radius = 25.0, float $thickness = 1.0): FlowerOfLife
    {
        return FlowerOfLife::create($radius, $thickness);
    }
}
