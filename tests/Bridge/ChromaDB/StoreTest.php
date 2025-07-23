<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\AI\Store\Tests\Bridge\ChromaDB;

use Codewithkyrian\ChromaDB\Client;
use Codewithkyrian\ChromaDB\Resources\CollectionResource;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Symfony\AI\Platform\Vector\Vector;
use Symfony\AI\Store\Bridge\ChromaDB\Store;
use Symfony\AI\Store\Document\Metadata;
use Symfony\AI\Store\Document\VectorDocument;
use Symfony\Component\Uid\Uuid;

#[CoversClass(Store::class)]
final class StoreTest extends TestCase
{
    #[Test]
    public function addDocumentsSuccessfully(): void
    {
        $collection = $this->createMock(CollectionResource::class);
        $client = $this->createMock(Client::class);

        $client->expects($this->once())
            ->method('getOrCreateCollection')
            ->with('test-collection')
            ->willReturn($collection);

        $uuid1 = Uuid::v4();
        $uuid2 = Uuid::v4();

        $collection->expects($this->once())
            ->method('add')
            ->with(
                [(string) $uuid1, (string) $uuid2],
                [[0.1, 0.2, 0.3], [0.4, 0.5, 0.6]],
                [[], ['title' => 'Test Document']],
            );

        $store = new Store($client, 'test-collection');

        $document1 = new VectorDocument($uuid1, new Vector([0.1, 0.2, 0.3]));
        $document2 = new VectorDocument($uuid2, new Vector([0.4, 0.5, 0.6]), new Metadata(['title' => 'Test Document']));

        $store->add($document1, $document2);
    }

    #[Test]
    public function addSingleDocument(): void
    {
        $collection = $this->createMock(CollectionResource::class);
        $client = $this->createMock(Client::class);

        $client->expects($this->once())
            ->method('getOrCreateCollection')
            ->with('test-collection')
            ->willReturn($collection);

        $uuid = Uuid::v4();

        $collection->expects($this->once())
            ->method('add')
            ->with(
                [(string) $uuid],
                [[0.1, 0.2, 0.3]],
                [['title' => 'Test Document', 'category' => 'test']],
            );

        $store = new Store($client, 'test-collection');

        $document = new VectorDocument($uuid, new Vector([0.1, 0.2, 0.3]), new Metadata(['title' => 'Test Document', 'category' => 'test']));

        $store->add($document);
    }
}
