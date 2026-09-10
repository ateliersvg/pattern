---
title: Atelier Pattern
description: Twenty-four repeating SVG tiles with geometry controls, styling and seeded variants.
order: 0
---

# atelier/pattern

`atelier/pattern` produces `<pattern>` tiles and the URL that references them. It renders no
image and draws nothing final.

```
Pattern::dots(spacing: 14, radius: 2.2)   a tile, geometry only
   -> withColor() withOpacity() withAngle()   style, immutably
   -> PatternRegistry::attachTo($document)    written into <defs>
   -> $pattern->fill()                        url(#id) for any fill attribute
```

## Documentation

- [Getting started](getting-started.md) -- fill a shape, colour it, pick a tile.
- [Patterns](patterns/index.md) -- the twenty-four tiles, one page each.

## What this package does not do

- No gradients. That is `atelier/svg`.
- No fill that depends on where it is applied.
- No bitmap output. That is `atelier/rasterizer`.
- No theme and no palette. A tile inherits, it does not decide.
