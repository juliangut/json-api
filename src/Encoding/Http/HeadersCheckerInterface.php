<?php

/*
 * (c) 2018-2024 Julián Gutiérrez <juliangut@gmail.com>
 *
 * @license BSD-3-Clause
 * @link https://github.com/juliangut/json-api
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
