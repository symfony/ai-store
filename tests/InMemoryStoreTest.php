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
    public function testStoreCanSearchUsingCosineDistance()
    {
        $store = new InMemoryStore();
        $store->add(
            new VectorDocument(Uuid::v4(), new Vector([0.1, 0.1, 0.5])),
            new VectorDocument(Uuid::v4(), new Vector([0.7, -0.3, 0.0])),
            new VectorDocument(Uuid::v4(), new Vector([0.3, 0.7, 0.1])),
        );

        $result = $store->query(new Vector([0.0, 0.1, 0.6]));
        $this->assertCount(3, $result);
        $this->assertSame([0.1, 0.1, 0.5], $result[0]->vector->getData());

        $store->add(
            new VectorDocument(Uuid::v4(), new Vector([0.1, 0.1, 0.5])),
            new VectorDocument(Uuid::v4(), new Vector([0.7, -0.3, 0.0])),
            new VectorDocument(Uuid::v4(), new Vector([0.3, 0.7, 0.1])),
        );

        $result = $store->query(new Vector([0.0, 0.1, 0.6]));
        $this->assertCount(6, $result);
        $this->assertSame([0.1, 0.1, 0.5], $result[0]->vector->getData());
    }

    public function testStoreCanSearchUsingCosineDistanceAndReturnCorrectOrder()
    {
        $store = new InMemoryStore();
        $store->add(
            new VectorDocument(Uuid::v4(), new Vector([0.1, 0.1, 0.5])),
            new VectorDocument(Uuid::v4(), new Vector([0.7, -0.3, 0.0])),
            new VectorDocument(Uuid::v4(), new Vector([0.3, 0.7, 0.1])),
            new VectorDocument(Uuid::v4(), new Vector([0.3, 0.1, 0.6])),
            new VectorDocument(Uuid::v4(), new Vector([0.0, 0.1, 0.6])),
        );

        $result = $store->query(new Vector([0.0, 0.1, 0.6]));
        $this->assertCount(5, $result);
        $this->assertSame([0.0, 0.1, 0.6], $result[0]->vector->getData());
        $this->assertSame([0.1, 0.1, 0.5], $result[1]->vector->getData());
        $this->assertSame([0.3, 0.1, 0.6], $result[2]->vector->getData());
        $this->assertSame([0.3, 0.7, 0.1], $result[3]->vector->getData());
        $this->assertSame([0.7, -0.3, 0.0], $result[4]->vector->getData());
    }

    public function testStoreCanSearchUsingCosineDistanceWithMaxItems()
    {
        $store = new InMemoryStore();
        $store->add(
            new VectorDocument(Uuid::v4(), new Vector([0.1, 0.1, 0.5])),
            new VectorDocument(Uuid::v4(), new Vector([0.7, -0.3, 0.0])),
            new VectorDocument(Uuid::v4(), new Vector([0.3, 0.7, 0.1])),
        );

        $this->assertCount(1, $store->query(new Vector([0.0, 0.1, 0.6]), [
            'maxItems' => 1,
        ]));
    }

    public function testStoreCanSearchUsingAngularDistance()
    {
        $store = new InMemoryStore(InMemoryStore::ANGULAR_DISTANCE);
        $store->add(
            new VectorDocument(Uuid::v4(), new Vector([1.0, 2.0, 3.0])),
            new VectorDocument(Uuid::v4(), new Vector([1.0, 5.0, 7.0])),
        );

        $result = $store->query(new Vector([1.2, 2.3, 3.4]));

        $this->assertCount(2, $result);
        $this->assertSame([1.0, 2.0, 3.0], $result[0]->vector->getData());
    }

    public function testStoreCanSearchUsingEuclideanDistance()
    {
        $store = new InMemoryStore(InMemoryStore::EUCLIDEAN_DISTANCE);
        $store->add(
            new VectorDocument(Uuid::v4(), new Vector([1.0, 5.0, 7.0])),
            new VectorDocument(Uuid::v4(), new Vector([1.0, 2.0, 3.0])),
        );

        $result = $store->query(new Vector([1.2, 2.3, 3.4]));

        $this->assertCount(2, $result);
        $this->assertSame([1.0, 2.0, 3.0], $result[0]->vector->getData());
    }

    public function testStoreCanSearchUsingManhattanDistance()
    {
        $store = new InMemoryStore(InMemoryStore::MANHATTAN_DISTANCE);
        $store->add(
            new VectorDocument(Uuid::v4(), new Vector([1.0, 2.0, 3.0])),
            new VectorDocument(Uuid::v4(), new Vector([1.0, 5.0, 7.0])),
        );

        $result = $store->query(new Vector([1.2, 2.3, 3.4]));

        $this->assertCount(2, $result);
        $this->assertSame([1.0, 2.0, 3.0], $result[0]->vector->getData());
    }

    public function testStoreCanSearchUsingChebyshevDistance()
    {
        $store = new InMemoryStore(InMemoryStore::CHEBYSHEV_DISTANCE);
        $store->add(
            new VectorDocument(Uuid::v4(), new Vector([1.0, 2.0, 3.0])),
            new VectorDocument(Uuid::v4(), new Vector([1.0, 5.0, 7.0])),
        );

        $result = $store->query(new Vector([1.2, 2.3, 3.4]));

        $this->assertCount(2, $result);
        $this->assertSame([1.0, 2.0, 3.0], $result[0]->vector->getData());
    }
}
