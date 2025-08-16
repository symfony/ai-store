<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\AI\Store\Tests\Bridge\ChromaDb;

use Codewithkyrian\ChromaDB\Client;
use Codewithkyrian\ChromaDB\Resources\CollectionResource;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;
use Symfony\AI\Platform\Vector\Vector;
use Symfony\AI\Store\Bridge\ChromaDb\Store;
use Symfony\AI\Store\Document\Metadata;
use Symfony\AI\Store\Document\VectorDocument;
use Symfony\Component\Uid\Uuid;

#[CoversClass(Store::class)]
#[UsesClass(VectorDocument::class)]
#[UsesClass(Vector::class)]
final class StoreTest extends TestCase
{
    /**
     * @param array<VectorDocument>       $documents
     * @param array<string>               $expectedIds
     * @param array<array<float>>         $expectedVectors
     * @param array<array<string, mixed>> $expectedMetadata
     * @param array<string>               $expectedOriginalDocuments
     */
    #[DataProvider('addDocumentsProvider')]
    public function testAddDocumentsSuccessfully(
        array $documents,
        array $expectedIds,
        array $expectedVectors,
        array $expectedMetadata,
        array $expectedOriginalDocuments,
    ): void {
        $collection = $this->createMock(CollectionResource::class);
        $client = $this->createMock(Client::class);

        $client->expects($this->once())
            ->method('getOrCreateCollection')
            ->with('test-collection')
            ->willReturn($collection);

        $collection->expects($this->once())
            ->method('add')
            ->with($expectedIds, $expectedVectors, $expectedMetadata, $expectedOriginalDocuments);

        $store = new Store($client, 'test-collection');

        $store->add(...$documents);
    }

    /**
     * @return \Iterator<string, array{
     *     documents: array<VectorDocument>,
     *     expectedIds: array<string>,
     *     expectedVectors: array<array<float>>,
     *     expectedMetadata: array<array<string, mixed>>,
     *     expectedOriginalDocuments: array<string>
     * }>
     */
    public static function addDocumentsProvider(): \Iterator
    {
        yield 'multiple documents with and without metadata' => [
            'documents' => [
                new VectorDocument(
                    Uuid::fromString('01234567-89ab-cdef-0123-456789abcdef'),
                    new Vector([0.1, 0.2, 0.3]),
                ),
                new VectorDocument(
                    Uuid::fromString('fedcba98-7654-3210-fedc-ba9876543210'),
                    new Vector([0.4, 0.5, 0.6]),
                    new Metadata(['title' => 'Test Document']),
                ),
            ],
            'expectedIds' => ['01234567-89ab-cdef-0123-456789abcdef', 'fedcba98-7654-3210-fedc-ba9876543210'],
            'expectedVectors' => [[0.1, 0.2, 0.3], [0.4, 0.5, 0.6]],
            'expectedMetadata' => [[], ['title' => 'Test Document']],
            'expectedOriginalDocuments' => ['', ''],
        ];

        yield 'single document with metadata' => [
            'documents' => [
                new VectorDocument(
                    Uuid::fromString('01234567-89ab-cdef-0123-456789abcdef'),
                    new Vector([0.1, 0.2, 0.3]),
                    new Metadata(['title' => 'Test Document', 'category' => 'test']),
                ),
            ],
            'expectedIds' => ['01234567-89ab-cdef-0123-456789abcdef'],
            'expectedVectors' => [[0.1, 0.2, 0.3]],
            'expectedMetadata' => [['title' => 'Test Document', 'category' => 'test']],
            'expectedOriginalDocuments' => [''],
        ];

        yield 'documents with text content' => [
            'documents' => [
                new VectorDocument(
                    Uuid::fromString('01234567-89ab-cdef-0123-456789abcdef'),
                    new Vector([0.1, 0.2, 0.3]),
                    new Metadata(['_text' => 'This is the content of document 1', 'title' => 'Document 1'])),
                new VectorDocument(
                    Uuid::fromString('fedcba98-7654-3210-fedc-ba9876543210'),
                    new Vector([0.4, 0.5, 0.6]),
                    new Metadata(['_text' => 'This is the content of document 2', 'title' => 'Document 2', 'category' => 'test']),
                ),
            ],
            'expectedIds' => ['01234567-89ab-cdef-0123-456789abcdef', 'fedcba98-7654-3210-fedc-ba9876543210'],
            'expectedVectors' => [[0.1, 0.2, 0.3], [0.4, 0.5, 0.6]],
            'expectedMetadata' => [['title' => 'Document 1'], ['title' => 'Document 2', 'category' => 'test']],
            'expectedOriginalDocuments' => ['This is the content of document 1', 'This is the content of document 2'],
        ];

        yield 'document with null text' => [
            'documents' => [
                new VectorDocument(
                    Uuid::fromString('01234567-89ab-cdef-0123-456789abcdef'),
                    new Vector([0.1, 0.2, 0.3]),
                    new Metadata(['_text' => null, 'title' => 'Test Document']),
                ),
            ],
            'expectedIds' => ['01234567-89ab-cdef-0123-456789abcdef'],
            'expectedVectors' => [[0.1, 0.2, 0.3]],
            'expectedMetadata' => [['title' => 'Test Document']],
            'expectedOriginalDocuments' => [''],
        ];
    }
}
