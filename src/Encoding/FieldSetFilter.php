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

namespace Jgut\JsonApi\Encoding;

use Neomerx\JsonApi\Representation\FieldSetFilter as BaseFieldSetFilter;

/**
 * Custom factory.
 */
class FieldSetFilter extends BaseFieldSetFilter
{
    /**
     * {@inheritdoc}
     */
    protected function filterFields(string $type, iterable $fields): iterable
    {
        if ($this->hasFilter($type) === false) {
            foreach ($fields as $name => $value) {
                if ($value instanceof \Closure) {
                    $value = $value();
                }

                yield $name => $value;
            }

            return;
        }

        $allowedFields = $this->getAllowedFields($type);
        foreach ($fields as $name => $value) {
            if (isset($allowedFields[$name]) === true) {
                if ($value instanceof \Closure) {
                    $value = $value();
                }

                yield $name => $value;
            }
        }
    }
}
