<?php

/*
 * json-api (https://github.com/juliangut/json-api).
 * PSR-7 aware json-api integration.
 *
 * @license BSD-3-Clause
 * @link https://github.com/juliangut/json-api
 * @author Julián Gutiérrez <juliangut@gmail.com>
 */

declare(strict_types=1);

namespace Jgut\JsonApi\Encoding\Http;

use Neomerx\JsonApi\Exceptions\JsonApiException;
use Psr\Http\Message\ServerRequestInterface;

interface HeadersCheckerInterface
{
    /**
     * Check request headers validity.
     *
     * @throws JsonApiException
     */
    public function checkHeaders(ServerRequestInterface $request): void;
}
