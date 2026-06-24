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
use Symfony\AI\Store\Exception\InvalidArgumentException;
use Symfony\AI\Store\Exception\RuntimeException;
use Symfony\Component\Finder\Finder;

/**
 * Scans a directory and delegates each file to a sub-loader chosen by its extension.
 *
 * The sub-loaders are injected as a map of lowercase file extension (without the leading dot) to a
 * {@see LoaderInterface}, e.g. `['md' => new MarkdownLoader(), 'json' => new JsonFileLoader(...)]`.
 * Files whose extension has no registered loader are skipped. Set `$recursive` to false to only load
 * the files located directly in the given directory.
 *
 * @author Christopher Hertel <mail@christopher-hertel.de>
 */
final class DirectoryLoader implements LoaderInterface
{
    /**
     * @var array<string, LoaderInterface>
     */
    private readonly array $loaders;

    /**
     * @param array<string, LoaderInterface> $loaders map of file extension (without leading dot) to the loader handling it
     */
    public function __construct(
        array $loaders,
        private readonly bool $recursive = true,
    ) {
        if ([] === $loaders) {
            throw new InvalidArgumentException('DirectoryLoader requires at least one extension loader.');
        }

        $normalized = [];
        foreach ($loaders as $extension => $loader) {
            $normalized[strtolower($extension)] = $loader;
        }

        $this->loaders = $normalized;
    }

    public function load(?string $source = null, array $options = []): iterable
    {
        if (!class_exists(Finder::class)) {
            throw new RuntimeException('For using the DirectoryLoader, the Symfony Finder component is required. Try running "composer require symfony/finder".');
        }

        if (null === $source || !is_dir($source)) {
            throw new InvalidArgumentException(\sprintf('DirectoryLoader requires an existing directory as source, "%s" given.', $source ?? 'null'));
        }

        $finder = (new Finder())
            ->files()
            ->in($source)
            ->sortByName();

        if (!$this->recursive) {
            $finder->depth(0);
        }

        foreach ($finder as $file) {
            $extension = strtolower($file->getExtension());
            if (!isset($this->loaders[$extension])) {
                continue;
            }

            yield from $this->loaders[$extension]->load($file->getRealPath(), $options);
        }
    }
}
