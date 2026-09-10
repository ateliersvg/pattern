<?php

declare(strict_types=1);

namespace Atelier\Pattern;

use Atelier\Svg\Element\Gradient\PatternElement;

/**
 * A repeatable tile usable as a fill or a stroke paint server.
 *
 * A pattern is an inert value: it needs no document to exist, and building one
 * mutates nothing. Every tile joins with itself, so anything leaving through one
 * edge comes back through the opposite edge.
 */
interface PatternInterface
{
    /**
     * The identifier the tile is published under, derived from its content
     * unless forced with withId().
     */
    public function id(): string;

    /**
     * A fresh <pattern> element carrying the tile, ready to be put in <defs>.
     */
    public function element(): PatternElement;

    /**
     * The paint reference to this pattern, as "url(#id)".
     */
    public function fill(): string;
}
