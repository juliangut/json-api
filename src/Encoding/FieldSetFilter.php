<?php

/*
 * (c) 2018-2023 Julián Gutiérrez <juliangut@gmail.com>
 *
 * @license BSD-3-Clause
 * @link https://github.com/juliangut/json-api
 */

declare(strict_types=1);

namespace Jgut\JsonApi\Encoding;

use Closure;
use Neomerx\JsonApi\Representation\FieldSetFilter as BaseFieldSetFilter;

class FieldSetFilter extends BaseFieldSetFilter
{
    /**
     * @param iterable<string, string|mixed> $fields
     *
     * @return iterable<string, string>
     */
    protected function filterFields(string $type, iterable $fields): iterable
    {
        if ($this->hasFilter($type) === false) {
            foreach ($fields as $name => $value) {
                if ($value instanceof Closure) {
                    $value = $value();
                }

                yield $name => $value;
            }

            return;
        }

        $allowedFields = $this->getAllowedFields($type);
        foreach ($fields as $name => $value) {
            if (\array_key_exists($name, $allowedFields) === true) {
                if ($value instanceof Closure) {
                    $value = $value();
                }

                yield $name => $value;
            }
        }
    }
}
