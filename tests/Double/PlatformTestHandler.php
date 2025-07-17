<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\AI\Store\Tests\Double;

use Symfony\AI\Platform\Model;
use Symfony\AI\Platform\ModelClientInterface;
use Symfony\AI\Platform\Platform;
use Symfony\AI\Platform\Response\RawHttpResponse;
use Symfony\AI\Platform\Response\RawResponseInterface;
use Symfony\AI\Platform\Response\ResponseInterface;
use Symfony\AI\Platform\Response\VectorResponse;
use Symfony\AI\Platform\ResponseConverterInterface;
use Symfony\AI\Platform\Vector\Vector;
use Symfony\Component\HttpClient\Response\MockResponse;

final class PlatformTestHandler implements ModelClientInterface, ResponseConverterInterface
{
    public int $createCalls = 0;

    public function __construct(
        private readonly ?ResponseInterface $create = null,
    ) {
    }

    public static function createPlatform(?ResponseInterface $create = null): Platform
    {
        $handler = new self($create);

        return new Platform([$handler], [$handler]);
    }

    public function supports(Model $model): bool
    {
        return true;
    }

    public function request(Model $model, array|string|object $payload, array $options = []): RawHttpResponse
    {
        ++$this->createCalls;

        return new RawHttpResponse(new MockResponse());
    }

    public function convert(RawResponseInterface $response, array $options = []): ResponseInterface
    {
        return $this->create ?? new VectorResponse(new Vector([1, 2, 3]));
    }
}
