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

namespace Jgut\JsonApi\Mapping\Metadata;

class RelationshipMetadata extends AbstractFieldMetadata
{
    use GroupTrait;
    use LinkTrait;
    use MetaTrait;

    /**
     * @param class-string<object> $class
     * @param array<string>        $groups
     */
    public function __construct(string $class, string $name, array $groups = [])
    {
        parent::__construct($class, $name);

        $this->groups = $groups;
    }
}
