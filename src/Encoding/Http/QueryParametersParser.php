<?php

/*
 * (c) 2018-2024 Julián Gutiérrez <juliangut@gmail.com>
 *
 * @license BSD-3-Clause
 * @link https://github.com/juliangut/json-api
 */

declare(strict_types=1);

namespace Jgut\JsonApi\Encoding\Http;

use Exception;
use Neomerx\JsonApi\Exceptions\JsonApiException;
use Neomerx\JsonApi\Schema\Error;
use TypeError;

class QueryParametersParser implements QueryParametersParserInterface
{
    /**
     * @var array<string>
     */
    protected array $knownParameters = [
        self::PARAM_FIELDS,
        self::PARAM_INCLUDE,
        self::PARAM_SORT,
        self::PARAM_PAGE,
        self::PARAM_FILTER,
    ];

    /**
     * @var array<non-empty-string>
     */
    protected array $fields = [];

    /**
     * @var array<non-empty-string>
     */
    protected array $includes = [];

    /**
     * @var array<string, bool>
     */
    protected array $sorts = [];

    /**
     * @var array<string, int>
     */
    protected array $paging = [];

    /**
     * @var mixed|array<string, mixed>
     */
    protected $filters;

    /**
     * @param array<mixed> $parameters
     *
     * @throws JsonApiException
     */
    public function __construct(array $parameters = [])
    {
        $this->parseParameters($parameters);
    }

    /**
     * Parse query parameters.
     *
     * @param array<string, mixed> $parameters
     *
     * @throws JsonApiException
     */
    public function parseParameters(array $parameters): void
    {
        foreach ($parameters as $parameter => $value) {
            if (\in_array($parameter, $this->knownParameters, true)) {
                try {
                    $method = 'parse' . ucfirst($parameter) . 'Parameter';

                    /** @var callable $callable */
                    $callable = [$this, $method];

                    $callable($value);
                } catch (TypeError $error) {
                    throw new JsonApiException(
                        $this->getJsonApiError(
                            'Invalid parameter',
                            sprintf('Parameter "%s" has an invalid value', $parameter),
                        ),
                        JsonApiException::HTTP_CODE_BAD_REQUEST,
                        new Exception($error->getMessage(), $error->getCode(), $error),
                    );
                }
            }
        }
    }

    /**
     * @return iterable<non-empty-string>
     */
    public function getFields(): iterable
    {
        return $this->fields;
    }

    /**
     * @param array<non-empty-string> $fields
     */
    public function setFields(array $fields): self
    {
        $this->fields = $fields;

        return $this;
    }

    /**
     * @param array<non-empty-string> $fields
     *
     * @throws JsonApiException
     */
    protected function parseFieldsParameter(array $fields): self
    {
        array_walk(
            $fields,
            function (&$fieldList, $resourceName): void {
                if (is_numeric($resourceName)) {
                    throw new JsonApiException($this->getJsonApiError(
                        'Invalid parameter',
                        sprintf('Parameter "%s" has an invalid value', self::PARAM_FIELDS),
                    ));
                }

                $fieldList = $this->splitString(self::PARAM_FIELDS, $fieldList, ',');
            },
        );

        $this->fields = $fields;

        return $this;
    }

    /**
     * @return iterable<non-empty-string>
     */
    public function getIncludes(): iterable
    {
        return $this->includes;
    }

    /**
     * @param array<non-empty-string> $includes
     */
    public function setIncludes(array $includes): self
    {
        $this->includes = $includes;

        return $this;
    }

    /**
     * @throws JsonApiException
     */
    public function parseIncludeParameter(string $includes): self
    {
        $this->includes = $this->splitString(self::PARAM_INCLUDE, $includes, ',');

        return $this;
    }

    /**
     * @return iterable<string, bool>
     */
    public function getSorts(): iterable
    {
        return $this->sorts;
    }

    /**
     * @param array<string, bool> $sorts
     */
    public function setSorts(array $sorts): self
    {
        $this->sorts = $sorts;

        return $this;
    }

    /**
     * @throws JsonApiException
     */
    public function parseSortParameter(string $sorts): self
    {
        $sortList = [];

        foreach ($this->splitString(self::PARAM_SORT, $sorts, ',') as $field) {
            $isAsc = $field[0] !== '-';
            if (\in_array($field[0], ['-', '+'], true)) {
                $field = mb_substr($field, 1);
            }

            $sortList[$field] = $isAsc;
        }

        $this->sorts = $sortList;

        return $this;
    }

    /**
     * @return array<string, int>
     */
    public function getPaging(): array
    {
        return $this->paging;
    }

    /**
     * @param array<string, int> $paging
     */
    public function setPaging(array $paging): self
    {
        $this->paging = $paging;

        return $this;
    }

    /**
     * @param array<string, mixed> $paging
     *
     * @throws JsonApiException
     */
    public function parsePageParameter(array $paging): self
    {
        array_walk(
            $paging,
            function (&$value, $key): void {
                if (is_numeric($key) || !is_numeric($value) || ($value + 0) !== (int) $value) {
                    throw new JsonApiException($this->getJsonApiError(
                        'Invalid parameter',
                        sprintf('Parameter %s has an invalid value', self::PARAM_PAGE),
                    ));
                }

                $value = (int) $value;
            },
        );

        /** @var array<string, int> $paging */
        $this->paging = $paging;

        return $this;
    }

    /**
     * @return mixed|array<string, mixed>
     */
    public function getFilters()
    {
        return $this->filters;
    }

    /**
     * @param mixed|array<string, mixed> $filters
     */
    public function setFilters($filters): self
    {
        $this->filters = $filters;

        return $this;
    }

    /**
     * @param mixed|array<string, mixed> $filters
     *
     * @throws JsonApiException
     */
    public function parseFilterParameter($filters): self
    {
        $this->filters = $filters;

        return $this;
    }

    /**
     * @param non-empty-string $separator
     *
     * @throws JsonApiException
     *
     * @return array<non-empty-string>
     */
    protected function splitString(string $parameter, string $string, string $separator): array
    {
        $strings = explode($separator, $string);

        return array_filter(array_map(
            function (string $value) use ($parameter): string {
                $value = trim($value);

                if ($value === '') {
                    throw new JsonApiException($this->getJsonApiError(
                        'Invalid parameter',
                        sprintf('Parameter "%s" has an invalid value', $parameter),
                    ));
                }

                return $value;
            },
            $strings,
        ));
    }

    protected function getJsonApiError(string $title, string $detail): Error
    {
        return new Error(
            null,
            null,
            null,
            (string) JsonApiException::HTTP_CODE_BAD_REQUEST,
            null,
            $title,
            $detail,
        );
    }
}
