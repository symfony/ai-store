<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\AI\Store\Tests\Bridge\Meilisearch;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Symfony\AI\Platform\Vector\Vector;
use Symfony\AI\Store\Bridge\Meilisearch\Store;
use Symfony\AI\Store\Document\VectorDocument;
use Symfony\Component\HttpClient\Exception\ClientException;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\MockResponse;
use Symfony\Component\Uid\Uuid;

#[CoversClass(Store::class)]
final class StoreTest extends TestCase
{
    public function testStoreCannotInitializeOnInvalidResponse(): void
    {
        $httpClient = new MockHttpClient([
            new MockResponse(json_encode([
                'message' => 'error',
                'code' => 'index_creation_failed',
                'type' => 'invalid_request',
                'link' => 'https://docs.meilisearch.com/errors#index_creation_failed',
            ]), [
                'http_code' => 400,
            ]),
        ], 'http://localhost:7700');

        $store = new Store(
            $httpClient,
            'http://localhost:7700',
            'test',
            'test',
        );

        self::expectException(ClientException::class);
        self::expectExceptionMessage('HTTP 400 returned for "http://localhost:7700/indexes".');
        self::expectExceptionCode(400);
        $store->initialize();
    }

    public function testStoreCanInitialize(): void
    {
        $httpClient = new MockHttpClient([
            new MockResponse(json_encode([
                'taskUid' => 1,
                'indexUid' => 'test',
                'status' => 'enqueued',
                'type' => 'indexCreation',
                'enqueuedAt' => '2025-01-01T00:00:00Z',
            ]), [
                'http_code' => 202,
            ]),
            new MockResponse(json_encode([
                'taskUid' => 2,
                'indexUid' => 'test',
                'status' => 'enqueued',
                'type' => 'indexUpdate',
                'enqueuedAt' => '2025-01-01T01:00:00Z',
            ]), [
                'http_code' => 202,
            ]),
        ], 'http://localhost:7700');

        $store = new Store(
            $httpClient,
            'http://localhost:7700',
            'test',
            'test',
        );

        $store->initialize();

        self::assertSame(2, $httpClient->getRequestsCount());
    }

    public function testStoreCannotAddOnInvalidResponse(): void
    {
        $httpClient = new MockHttpClient([
            new MockResponse(json_encode([
                'message' => 'error',
                'code' => 'invalid_document_fields',
                'type' => 'invalid_request',
                'link' => 'https://docs.meilisearch.com/errors#invalid_document_fields',
            ]), [
                'http_code' => 400,
            ]),
        ], 'http://localhost:7700');

        $store = new Store(
            $httpClient,
            'http://localhost:7700',
            'test',
            'test',
        );

        self::expectException(ClientException::class);
        self::expectExceptionMessage('HTTP 400 returned for "http://localhost:7700/indexes/test/documents".');
        self::expectExceptionCode(400);
        $store->add(new VectorDocument(Uuid::v4(), new Vector([0.1, 0.2, 0.3])));
    }

    public function testStoreCanAdd(): void
    {
        $httpClient = new MockHttpClient([
            new MockResponse(json_encode([
                'taskUid' => 1,
                'indexUid' => 'test',
                'status' => 'enqueued',
                'type' => 'documentAdditionOrUpdate',
                'enqueuedAt' => '2025-01-01T00:00:00Z',
            ]), [
                'http_code' => 202,
            ]),
        ], 'http://localhost:7700');

        $store = new Store(
            $httpClient,
            'http://localhost:7700',
            'test',
            'test',
        );

        $store->add(new VectorDocument(Uuid::v4(), new Vector([0.1, 0.2, 0.3])));

        self::assertSame(1, $httpClient->getRequestsCount());
    }

    public function testStoreCannotQueryOnInvalidResponse(): void
    {
        $httpClient = new MockHttpClient([
            new MockResponse(json_encode([
                'message' => 'error',
                'code' => 'invalid_search_hybrid_query',
                'type' => 'invalid_request',
                'link' => 'https://docs.meilisearch.com/errors#invalid_search_hybrid_query',
            ]), [
                'http_code' => 400,
            ]),
        ], 'http://localhost:7700');

        $store = new Store(
            $httpClient,
            'http://localhost:7700',
            'test',
            'test',
        );

        self::expectException(ClientException::class);
        self::expectExceptionMessage('HTTP 400 returned for "http://localhost:7700/indexes/test/search".');
        self::expectExceptionCode(400);
        $store->query(new Vector([0.1, 0.2, 0.3]));
    }

    public function testStoreCanQuery(): void
    {
        $httpClient = new MockHttpClient([
            new MockResponse(json_encode([
                'hits' => [
                    [
                        'id' => Uuid::v4()->toRfc4122(),
                        '_vectors' => [
                            'default' => [
                                'embeddings' => [0.1, 0.2, 0.3],
                                'regenerate' => false,
                            ],
                        ],
                    ],
                    [
                        'id' => Uuid::v4()->toRfc4122(),
                        '_vectors' => [
                            'default' => [
                                'embeddings' => [0.4, 0.5, 0.6],
                                'regenerate' => false,
                            ],
                        ],
                    ],
                ],
            ]), [
                'http_code' => 200,
            ]),
        ], 'http://localhost:7700');

        $store = new Store(
            $httpClient,
            'http://localhost:7700',
            'test',
            'test',
            embeddingsDimension: 3,
        );

        $vectors = $store->query(new Vector([0.1, 0.2, 0.3]));

        self::assertSame(1, $httpClient->getRequestsCount());
        self::assertCount(2, $vectors);
    }
}
