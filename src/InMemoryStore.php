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
use Symfony\AI\Store\Exception\InvalidArgumentException;

/**
 * @author Guillaume Loulier <personal@guillaumeloulier.fr>
 */
final class InMemoryStore implements VectorStoreInterface
{
    public const COSINE_DISTANCE = 'cosine';
    public const ANGULAR_DISTANCE = 'angular';
    public const EUCLIDEAN_DISTANCE = 'euclidean';
    public const MANHATTAN_DISTANCE = 'manhattan';
    public const CHEBYSHEV_DISTANCE = 'chebyshev';

    /**
     * @var VectorDocument[]
     */
    private array $documents = [];

    public function __construct(
        private readonly string $distance = self::COSINE_DISTANCE,
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
    public function query(Vector $vector, array $options = [], ?float $minScore = null): array
    {
        $strategy = match ($this->distance) {
            self::COSINE_DISTANCE => $this->cosineDistance(...),
            self::ANGULAR_DISTANCE => $this->angularDistance(...),
            self::EUCLIDEAN_DISTANCE => $this->euclideanDistance(...),
            self::MANHATTAN_DISTANCE => $this->manhattanDistance(...),
            self::CHEBYSHEV_DISTANCE => $this->chebyshevDistance(...),
            default => throw new InvalidArgumentException(\sprintf('Unsupported distance metric "%s"', $this->distance)),
        };

        $currentEmbeddings = array_map(
            static fn (VectorDocument $vectorDocument): array => [
                'distance' => $strategy($vectorDocument, $vector),
                'document' => $vectorDocument,
            ],
            $this->documents,
        );

        usort(
            $currentEmbeddings,
            static fn (array $embedding, array $nextEmbedding): int => $embedding['distance'] <=> $nextEmbedding['distance'],
        );

        if (\array_key_exists('maxItems', $options) && $options['maxItems'] < \count($currentEmbeddings)) {
            $currentEmbeddings = \array_slice($currentEmbeddings, 0, $options['maxItems']);
        }

        return array_map(
            static fn (array $embedding): VectorDocument => $embedding['document'],
            $currentEmbeddings,
        );
    }

    private function cosineDistance(VectorDocument $embedding, Vector $against): float
    {
        return 1 - $this->cosineSimilarity($embedding, $against);
    }

    private function cosineSimilarity(VectorDocument $embedding, Vector $against): float
    {
        $currentEmbeddingVectors = $embedding->vector->getData();

        $dotProduct = array_sum(array: array_map(
            static fn (float $a, float $b): float => $a * $b,
            $currentEmbeddingVectors,
            $against->getData(),
        ));

        $currentEmbeddingLength = sqrt(array_sum(array_map(
            static fn (float $value): float => $value ** 2,
            $currentEmbeddingVectors,
        )));

        $againstLength = sqrt(array_sum(array_map(
            static fn (float $value): float => $value ** 2,
            $against->getData(),
        )));

        return fdiv($dotProduct, $currentEmbeddingLength * $againstLength);
    }

    private function angularDistance(VectorDocument $embedding, Vector $against): float
    {
        $cosineSimilarity = $this->cosineSimilarity($embedding, $against);

        return fdiv(acos($cosineSimilarity), \M_PI);
    }

    private function euclideanDistance(VectorDocument $embedding, Vector $against): float
    {
        return sqrt(array_sum(array_map(
            static fn (float $a, float $b): float => ($a - $b) ** 2,
            $embedding->vector->getData(),
            $against->getData(),
        )));
    }

    private function manhattanDistance(VectorDocument $embedding, Vector $against): float
    {
        return array_sum(array_map(
            static fn (float $a, float $b): float => abs($a - $b),
            $embedding->vector->getData(),
            $against->getData(),
        ));
    }

    private function chebyshevDistance(VectorDocument $embedding, Vector $against): float
    {
        $embeddingsAsPower = array_map(
            static fn (float $currentValue, float $againstValue): float => abs($currentValue - $againstValue),
            $embedding->vector->getData(),
            $against->getData(),
        );

        return array_reduce(
            array: $embeddingsAsPower,
            callback: static fn (float $value, float $current): float => max($value, $current),
            initial: 0.0,
        );
    }
}
