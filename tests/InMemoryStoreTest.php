<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\AI\Store\Tests;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Symfony\AI\Platform\Vector\Vector;
use Symfony\AI\Store\Document\VectorDocument;
use Symfony\AI\Store\InMemoryStore;
use Symfony\Component\Uid\Uuid;

#[CoversClass(InMemoryStore::class)]
final class InMemoryStoreTest extends TestCase
{
    public function testStoreCanSearchUsingCosineSimilarity(): void
    {
        $store = new InMemoryStore();
        $store->add(
            new VectorDocument(Uuid::v4(), new Vector([0.1, 0.1, 0.5])),
            new VectorDocument(Uuid::v4(), new Vector([0.7, -0.3, 0.0])),
            new VectorDocument(Uuid::v4(), new Vector([0.3, 0.7, 0.1])),
        );

        self::assertCount(3, $store->query(new Vector([0.0, 0.1, 0.6])));

        $store->add(
            new VectorDocument(Uuid::v4(), new Vector([0.1, 0.1, 0.5])),
            new VectorDocument(Uuid::v4(), new Vector([0.7, -0.3, 0.0])),
            new VectorDocument(Uuid::v4(), new Vector([0.3, 0.7, 0.1])),
        );

        self::assertCount(6, $store->query(new Vector([0.0, 0.1, 0.6])));
    }

    public function testStoreCanSearchUsingCosineSimilarityWithMaxItems(): void
    {
        $store = new InMemoryStore();
        $store->add(
            new VectorDocument(Uuid::v4(), new Vector([0.1, 0.1, 0.5])),
            new VectorDocument(Uuid::v4(), new Vector([0.7, -0.3, 0.0])),
            new VectorDocument(Uuid::v4(), new Vector([0.3, 0.7, 0.1])),
        );

        self::assertCount(1, $store->query(new Vector([0.0, 0.1, 0.6]), [
            'maxItems' => 1,
        ]));
    }

    public function testStoreCanSearchUsingAngularDistance(): void
    {
        $store = new InMemoryStore(InMemoryStore::ANGULAR_DISTANCE);
        $store->add(
            new VectorDocument(Uuid::v4(), new Vector([1.0, 2.0, 3.0])),
            new VectorDocument(Uuid::v4(), new Vector([1.0, 5.0, 7.0])),
        );

        $result = $store->query(new Vector([1.2, 2.3, 3.4]));

        self::assertCount(2, $result);
        self::assertSame([1.0, 2.0, 3.0], $result[0]->vector->getData());
    }

    public function testStoreCanSearchUsingEuclideanDistance(): void
    {
        $store = new InMemoryStore(InMemoryStore::EUCLIDEAN_DISTANCE);
        $store->add(
            new VectorDocument(Uuid::v4(), new Vector([1.0, 5.0, 7.0])),
            new VectorDocument(Uuid::v4(), new Vector([1.0, 2.0, 3.0])),
        );

        $result = $store->query(new Vector([1.2, 2.3, 3.4]));

        self::assertCount(2, $result);
        self::assertSame([1.0, 2.0, 3.0], $result[0]->vector->getData());
    }

    public function testStoreCanSearchUsingManhattanDistance(): void
    {
        $store = new InMemoryStore(InMemoryStore::MANHATTAN_DISTANCE);
        $store->add(
            new VectorDocument(Uuid::v4(), new Vector([1.0, 2.0, 3.0])),
            new VectorDocument(Uuid::v4(), new Vector([1.0, 5.0, 7.0])),
        );

        $result = $store->query(new Vector([1.2, 2.3, 3.4]));

        self::assertCount(2, $result);
        self::assertSame([1.0, 2.0, 3.0], $result[0]->vector->getData());
    }

    public function testStoreCanSearchUsingChebyshevDistance(): void
    {
        $store = new InMemoryStore(InMemoryStore::CHEBYSHEV_DISTANCE);
        $store->add(
            new VectorDocument(Uuid::v4(), new Vector([1.0, 2.0, 3.0])),
            new VectorDocument(Uuid::v4(), new Vector([1.0, 5.0, 7.0])),
        );

        $result = $store->query(new Vector([1.2, 2.3, 3.4]));

        self::assertCount(2, $result);
        self::assertSame([1.0, 2.0, 3.0], $result[0]->vector->getData());
    }
}
