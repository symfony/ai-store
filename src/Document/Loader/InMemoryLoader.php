<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\AI\Store\Document\Loader;

use Symfony\AI\Store\Document\LoaderInterface;
use Symfony\AI\Store\Document\TextDocument;

/**
 * Loader that returns pre-loaded TextDocuments from memory.
 * Useful for testing or when documents are already available as objects.
 *
 * @author Oskar Stark <oskarstark@googlemail.com>
 */
final readonly class InMemoryLoader implements LoaderInterface
{
    /**
     * @param TextDocument[] $documents
     */
    public function __construct(
        private array $documents = [],
    ) {
    }

    public function load(string $source, array $options = []): iterable
    {
        yield from $this->documents;
    }
}
