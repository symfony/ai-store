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

final readonly class Vectorizer implements VectorizerInterface
{
    public function __construct(
        private PlatformInterface $platform,
        private Model $model,
    ) {
    }

    public function __invoke(array $documents): array
    {
        if ($this->model->supports(Capability::INPUT_MULTIPLE)) {
            $result = $this->platform->invoke($this->model, array_map(fn (TextDocument $document) => $document->content, $documents));

            $vectors = $result->asVectors();
        } else {
            $results = [];
            foreach ($documents as $document) {
                $results[] = $this->platform->invoke($this->model, $document->content);
            }

            $vectors = [];
            foreach ($results as $result) {
                $vectors = array_merge($vectors, $result->asVectors());
            }
        }

        $vectorDocuments = [];
        foreach ($documents as $i => $document) {
            $vectorDocuments[] = new VectorDocument($document->id, $vectors[$i], $document->metadata);
        }

        return $vectorDocuments;
    }
}
