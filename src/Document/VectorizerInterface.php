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

use Symfony\AI\Platform\Vector\Vector;

/**
 * Interface for converting a collection of TextDocuments into VectorDocuments
 * and for vectorizing individual strings.
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
    public function vectorizeTextDocuments(array $documents): array;

    /**
     * Vectorizes a single string into a Vector.
     */
    public function vectorize(string $string): Vector;
}
