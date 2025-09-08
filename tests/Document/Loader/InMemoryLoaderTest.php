<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\AI\Store\Tests\Document\Loader;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Symfony\AI\Store\Document\Loader\InMemoryLoader;
use Symfony\AI\Store\Document\Metadata;
use Symfony\AI\Store\Document\TextDocument;
use Symfony\Component\Uid\Uuid;

/**
 * @author Oskar Stark <oskarstark@googlemail.com>
 */
#[CoversClass(InMemoryLoader::class)]
final class InMemoryLoaderTest extends TestCase
{
    public function testLoadWithEmptyDocuments()
    {
        $loader = new InMemoryLoader();
        $documents = iterator_to_array($loader->load('ignored-source'));

        $this->assertEmpty($documents);
    }

    public function testLoadWithSingleDocument()
    {
        $document = new TextDocument(Uuid::v4(), 'This is test content');
        $loader = new InMemoryLoader([$document]);

        $documents = iterator_to_array($loader->load('ignored-source'));

        $this->assertCount(1, $documents);
        $this->assertSame($document, $documents[0]);
        $this->assertSame('This is test content', $documents[0]->content);
    }

    public function testLoadWithMultipleDocuments()
    {
        $document1 = new TextDocument(Uuid::v4(), 'First document');
        $document2 = new TextDocument(Uuid::v4(), 'Second document', new Metadata(['type' => 'test']));
        $loader = new InMemoryLoader([$document1, $document2]);

        $documents = iterator_to_array($loader->load('ignored-source'));

        $this->assertCount(2, $documents);
        $this->assertSame($document1, $documents[0]);
        $this->assertSame($document2, $documents[1]);
        $this->assertSame('First document', $documents[0]->content);
        $this->assertSame('Second document', $documents[1]->content);
        $this->assertSame('test', $documents[1]->metadata['type']);
    }

    public function testLoadIgnoresSourceParameter()
    {
        $document = new TextDocument(Uuid::v4(), 'test content');
        $loader = new InMemoryLoader([$document]);

        $documents1 = iterator_to_array($loader->load('source1'));
        $documents2 = iterator_to_array($loader->load('source2'));
        $documents3 = iterator_to_array($loader->load('any-source'));

        $this->assertCount(1, $documents1);
        $this->assertCount(1, $documents2);
        $this->assertCount(1, $documents3);
        $this->assertSame($document, $documents1[0]);
        $this->assertSame($document, $documents2[0]);
        $this->assertSame($document, $documents3[0]);
    }
}
