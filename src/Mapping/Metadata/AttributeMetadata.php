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

final class AttributeMetadata extends AbstractFieldMetadata
{
    /**
     * @param class-string<object> $class
     */
    public function __construct(
        string $class,
        string $name,
        /**
         * @var list<string>
         */
        protected array $groups = [],
    ) {
        parent::__construct($class, $name);
    }

    /**
     * @return list<string>
     */
    public function getGroups(): array
    {
        return $this->groups;
    }

    /**
     * @param list<string> $groups
     */
    public function setGroups(array $groups): self
    {
        $this->groups = $groups;

        return $this;
    }
}
