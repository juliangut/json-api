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

namespace Jgut\JsonApi\Mapping\Annotation;

trait GroupTrait
{
    /**
     * @var list<string>
     */
    protected array $groups = [];

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
