<?php

declare(strict_types=1);

namespace Atelier\Pattern;

use Atelier\Pattern\Internal\Guard;
use Atelier\Pattern\Internal\Num;
use Atelier\Svg\Element\ElementInterface;
use Atelier\Svg\Element\Gradient\PatternElement;
use Atelier\Svg\Element\Structural\GroupElement;

/**
 * Shared behaviour of every tile: identity, style, and assembly of the
 * <pattern> element.
 *
 * Subclasses hold the geometry and describe the shapes of one tile. Style
 * (color, opacity, angle) lives here and is applied on top, so a factory only
 * ever takes measurements.
 */
abstract class AbstractPattern implements PatternInterface
{
    private string $color = 'currentColor';

    private ?float $opacity = null;

    private float $angle = 0.0;

    private ?string $forcedId = null;

    /**
     * Width of one tile, in user units.
     */
    abstract public function tileWidth(): float;

    /**
     * Height of one tile, in user units.
     */
    abstract public function tileHeight(): float;

    /**
     * The shapes of one tile, already painted.
     *
     * Shapes crossing an edge come with their translate on the opposite edge:
     * the renderer clips the tile, and the neighbour supplies what was cut.
     *
     * @return list<ElementInterface>
     */
    abstract protected function shapes(): array;

    /**
     * The geometry values that identify this tile, in a stable order.
     *
     * @return list<int|float>
     */
    abstract protected function geometry(): array;

    /**
     * The readable half of the derived identifier. Fixed per tile, so renaming
     * a class never silently changes the identifiers a document already uses.
     */
    abstract protected function slug(): string;

    public function id(): string
    {
        if (null !== $this->forcedId) {
            return $this->forcedId;
        }

        // Encode floats at their full precision and integers without conversion.
        // Coordinate formatting is lossy and must not define pattern identity.
        $payload = serialize([
            static::class,
            $this->color,
            null === $this->opacity ? null : pack('E', $this->opacity),
            pack('E', $this->angle),
            ...array_map(static fn (int|float $value): int|string => \is_int($value) ? $value : pack('E', $value), $this->geometry()),
        ]);

        return $this->slug().'-'.hash('xxh128', $payload);
    }

    public function element(): PatternElement
    {
        $pattern = new PatternElement();
        $pattern->setId($this->id());
        $pattern->setPatternUnits('userSpaceOnUse');
        $pattern->setWidth(Num::format($this->tileWidth()));
        $pattern->setHeight(Num::format($this->tileHeight()));

        if (0.0 !== $this->angle) {
            $pattern->setPatternTransform('rotate('.Num::format($this->angle).')');
        }

        $shapes = $this->shapes();

        if (null === $this->opacity) {
            foreach ($shapes as $shape) {
                $pattern->appendChild($shape);
            }

            return $pattern;
        }

        // One group carries the opacity: shapes that overlap inside a tile keep
        // a single flat tone instead of darkening where they cross.
        $group = new GroupElement();
        $group->setAttribute('opacity', Num::format($this->opacity));

        foreach ($shapes as $shape) {
            $group->appendChild($shape);
        }

        $pattern->appendChild($group);

        return $pattern;
    }

    public function fill(): string
    {
        return 'url(#'.$this->id().')';
    }

    /**
     * The paint applied to the tile shapes. Defaults to currentColor, so one
     * tile serves every hue and follows the theme of its host.
     */
    public function color(): string
    {
        return $this->color;
    }

    /**
     * The tile opacity, or null when the attribute is left out entirely.
     */
    public function opacity(): ?float
    {
        return $this->opacity;
    }

    /**
     * The rotation applied to the whole tiling, in degrees.
     */
    public function angle(): float
    {
        return $this->angle;
    }

    public function withColor(string $color): static
    {
        Guard::notBlank($color, 'color');

        $clone = clone $this;
        $clone->color = $color;

        return $clone;
    }

    public function withOpacity(?float $opacity): static
    {
        if (null !== $opacity) {
            Guard::between($opacity, 0.0, 1.0, 'opacity');
        }

        $clone = clone $this;
        $clone->opacity = $opacity;

        return $clone;
    }

    /**
     * Rotates the tiling through patternTransform, so the rotation carries the
     * whole pavement and needs no oversized tile.
     */
    public function withAngle(float $angle): static
    {
        Guard::finite($angle, 'angle');

        $clone = clone $this;
        $clone->angle = $angle;

        return $clone;
    }

    /**
     * Forces the identifier instead of deriving it from the tile content.
     */
    public function withId(string $id): static
    {
        Guard::notBlank($id, 'id');

        $clone = clone $this;
        $clone->forcedId = $id;

        return $clone;
    }

    /**
     * Paints a shape with the pattern color.
     *
     * @template T of ElementInterface
     *
     * @param T $shape
     *
     * @return T
     */
    final protected function painted(ElementInterface $shape): ElementInterface
    {
        $shape->setAttribute('fill', $this->color);

        return $shape;
    }

    /**
     * Outlines a shape with the pattern color at the given thickness.
     *
     * @template T of ElementInterface
     *
     * @param T $shape
     *
     * @return T
     */
    final protected function outlined(ElementInterface $shape, float $thickness): ElementInterface
    {
        $shape->setAttribute('fill', 'none');
        $shape->setAttribute('stroke', $this->color);
        $shape->setAttribute('stroke-width', Num::format($thickness));

        return $shape;
    }
}
