<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\AI\Store;

use Symfony\AI\Platform\Vector\Vector;
use Symfony\AI\Store\Document\VectorDocument;

/**
 * @author Guillaume Loulier <personal@guillaumeloulier.fr>
 */
final class InMemoryStore implements StoreInterface
{
    /**
     * @var VectorDocument[]
     */
    private array $documents = [];

    public function __construct(
        private readonly DistanceCalculator $distanceCalculator = new DistanceCalculator(),
    ) {
    }

    public function add(VectorDocument ...$documents): void
    {
        array_push($this->documents, ...$documents);
    }

    /**
     * @param array{
     *     maxItems?: positive-int
     * } $options If maxItems is provided, only the top N results will be returned
     */
    public function query(Vector $vector, array $options = []): array
    {
        return $this->distanceCalculator->calculate($this->documents, $vector, $options['maxItems'] ?? null);
    }
}
