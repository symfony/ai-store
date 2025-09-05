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

/**
 * Interface for converting a collection of TextDocuments into VectorDocuments.
 *
 * @author Oskar Stark <oskarstark@googlemail.com>
 */
interface VectorizerInterface
{
    /**
     * @param TextDocument[] $documents
     *
     * @return VectorDocument[]
     */
    public function vectorizeDocuments(array $documents): array;
}
