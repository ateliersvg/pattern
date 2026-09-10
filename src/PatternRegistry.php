<?php

declare(strict_types=1);

namespace Atelier\Pattern;

use Atelier\Pattern\Exception\InvalidArgumentException;
use Atelier\Svg\Document;
use Atelier\Svg\Element\ContainerElementInterface;
use Atelier\Svg\Element\ElementInterface;
use Atelier\Svg\Element\Structural\DefsElement;
use Atelier\Svg\Element\SvgElement;

/**
 * Collects patterns and puts them in the <defs> of a document.
 *
 * Patterns are held by identifier, so a tile added twice is defined once.
 */
final class PatternRegistry implements \Countable
{
    /** @var array<string, PatternInterface> */
    private array $patterns = [];

    public function __construct(PatternInterface ...$patterns)
    {
        $this->add(...$patterns);
    }

    /**
     * Adds patterns, keeping the first one registered under a given identifier.
     */
    public function add(PatternInterface ...$patterns): self
    {
        foreach ($patterns as $pattern) {
            $this->patterns[$pattern->id()] ??= $pattern;
        }

        return $this;
    }

    public function has(string $id): bool
    {
        return isset($this->patterns[$id]);
    }

    public function get(string $id): ?PatternInterface
    {
        return $this->patterns[$id] ?? null;
    }

    /**
     * @return array<string, PatternInterface>
     */
    public function all(): array
    {
        return $this->patterns;
    }

    public function count(): int
    {
        return \count($this->patterns);
    }

    /**
     * Writes every pattern into the <defs> of the document, creating the
     * element if the document has none.
     *
     * A pattern whose identifier is already defined is left alone, so calling
     * this twice on the same document changes nothing.
     *
     * @throws InvalidArgumentException if the document has no root element
     */
    public function attachTo(Document $document): self
    {
        $root = $document->getRootElement();

        if (null === $root) {
            throw new InvalidArgumentException('The document has no root element to attach patterns to.');
        }

        $defs = $this->defsOf($root);

        foreach ($this->patterns as $id => $pattern) {
            if (null !== $document->getElementById($id) || null !== $this->childById($defs, $id)) {
                continue;
            }

            $defs->appendChild($pattern->element());
        }

        return $this;
    }

    private function defsOf(SvgElement $root): DefsElement
    {
        foreach ($root->getChildren() as $child) {
            if ($child instanceof DefsElement) {
                return $child;
            }
        }

        $defs = new DefsElement();
        $root->prependChild($defs);

        return $defs;
    }

    private function childById(ContainerElementInterface $defs, string $id): ?ElementInterface
    {
        foreach ($defs->getChildren() as $child) {
            if ($child->getId() === $id) {
                return $child;
            }
        }

        return null;
    }
}
