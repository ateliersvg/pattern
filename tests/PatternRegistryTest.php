<?php

declare(strict_types=1);

namespace Atelier\Pattern\Tests;

use Atelier\Pattern\Exception\InvalidArgumentException;
use Atelier\Pattern\Pattern;
use Atelier\Pattern\PatternRegistry;
use Atelier\Svg\Document;
use Atelier\Svg\Element\Structural\DefsElement;
use Atelier\Svg\Element\SvgElement;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(PatternRegistry::class)]
final class PatternRegistryTest extends TestCase
{
    public function testItStartsEmpty(): void
    {
        $registry = new PatternRegistry();

        self::assertCount(0, $registry);
        self::assertSame([], $registry->all());
    }

    public function testItTakesPatternsAtConstruction(): void
    {
        $registry = new PatternRegistry(Pattern::dots(), Pattern::stripes());

        self::assertCount(2, $registry);
    }

    public function testIdenticallyBuiltPatternsAreHeldOnce(): void
    {
        $registry = new PatternRegistry();
        $registry->add(Pattern::dots(spacing: 14), Pattern::dots(spacing: 14));

        self::assertCount(1, $registry);
    }

    public function testItLooksPatternsUpByIdentifier(): void
    {
        $dots = Pattern::dots();
        $registry = new PatternRegistry($dots);

        self::assertTrue($registry->has($dots->id()));
        self::assertSame($dots, $registry->get($dots->id()));
        self::assertFalse($registry->has('missing'));
        self::assertNull($registry->get('missing'));
    }

    public function testItCreatesTheDefsItNeeds(): void
    {
        $document = Document::create(100, 100);
        $dots = Pattern::dots();

        (new PatternRegistry($dots))->attachTo($document);

        $defs = $this->defsOf($document);

        self::assertCount(1, $defs->getChildren());
        self::assertSame($dots->id(), $defs->getChildren()[0]->getId());
    }

    public function testItReusesAnExistingDefs(): void
    {
        $document = Document::create(100, 100);
        $root = $document->getRootElement();
        self::assertInstanceOf(SvgElement::class, $root);

        $defs = new DefsElement();
        $root->appendChild($defs);

        (new PatternRegistry(Pattern::dots(), Pattern::stripes()))->attachTo($document);

        self::assertSame($defs, $this->defsOf($document));
        self::assertCount(2, $defs->getChildren());
    }

    public function testAttachingTwiceDefinesEachTileOnce(): void
    {
        $document = Document::create(100, 100);
        $registry = new PatternRegistry(Pattern::dots());

        $registry->attachTo($document);
        $registry->attachTo($document);

        self::assertCount(1, $this->defsOf($document)->getChildren());
    }

    public function testAPatternAlreadyInTheDocumentIsLeftAlone(): void
    {
        $dots = Pattern::dots();
        $document = Document::create(100, 100);
        $root = $document->getRootElement();
        self::assertInstanceOf(SvgElement::class, $root);

        $existing = $dots->element();
        $defs = new DefsElement();
        $defs->appendChild($existing);
        $root->appendChild($defs);
        $document->registerElementId($dots->id(), $existing);

        (new PatternRegistry($dots))->attachTo($document);

        self::assertSame([$existing], $defs->getChildren());
    }

    public function testAddAndAttachAreChainable(): void
    {
        $registry = new PatternRegistry();
        $document = Document::create(100, 100);

        self::assertSame($registry, $registry->add(Pattern::dots()));
        self::assertSame($registry, $registry->attachTo($document));
    }

    public function testADocumentWithoutARootIsRejected(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('The document has no root element to attach patterns to.');

        (new PatternRegistry(Pattern::dots()))->attachTo(new Document());
    }

    private function defsOf(Document $document): DefsElement
    {
        $root = $document->getRootElement();
        self::assertInstanceOf(SvgElement::class, $root);

        foreach ($root->getChildren() as $child) {
            if ($child instanceof DefsElement) {
                return $child;
            }
        }

        self::fail('The document has no defs.');
    }
}
