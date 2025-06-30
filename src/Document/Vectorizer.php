<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\AI\Store\Document;

use Symfony\AI\Platform\Capability;
use Symfony\AI\Platform\Model;
use Symfony\AI\Platform\PlatformInterface;

/**
 * The Vectorizer encapsulates the logic to convert a collection of TextDocuments into VectorDocuments. It checks for
 * the model's capabilities to handle batch processing or handles it with HttpClient's concurrency feature.
 */
final readonly class Vectorizer
{
    public function __construct(
        private PlatformInterface $platform,
        private Model $model,
    ) {
    }

    /**
     * @param TextDocument[] $documents
     *
     * @return VectorDocument[]
     */
    public function vectorizeDocuments(array $documents): array
    {
        if ($this->model->supports(Capability::INPUT_MULTIPLE)) {
            $response = $this->platform->request($this->model, array_map(fn (TextDocument $document) => $document->content, $documents));

            $vectors = $response->getContent();
        } else {
            $responses = [];
            foreach ($documents as $document) {
                $responses[] = $this->platform->request($this->model, $document->content);
            }

            $vectors = [];
            foreach ($responses as $response) {
                $vectors = array_merge($vectors, $response->getContent());
            }
        }

        $vectorDocuments = [];
        foreach ($documents as $i => $document) {
            $vectorDocuments[] = new VectorDocument($document->id, $vectors[$i], $document->metadata);
        }

        return $vectorDocuments;
    }
}
