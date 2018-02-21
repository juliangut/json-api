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

use Neomerx\JsonApi\Contracts\Http\Query\BaseQueryParserInterface;
use Neomerx\JsonApi\Document\Error;
use Neomerx\JsonApi\Exceptions\JsonApiException;

/**
 * Request query parameters parser.
 */
class QueryParametersParser implements BaseQueryParserInterface
{
    /**
     * Known query parameters.
     *
     * @var array
     */
    protected $knownParameters = [
        self::PARAM_FIELDS,
        self::PARAM_INCLUDE,
        self::PARAM_SORT,
        self::PARAM_PAGE,
        self::PARAM_FILTER,
    ];

    /**
     * Query field sets.
     *
     * @var array
     */
    protected $fields = [];

    /**
     * Query includes list.
     *
     * @var array
     */
    protected $includes = [];

    /**
     * Query sorting.
     *
     * @var array
     */
    protected $sorts = [];

    /**
     * Query paging.
     *
     * @var array
     */
    protected $paging = [];

    /**
     * Query filters.
     *
     * @var mixed
     */
    protected $filters;

    /**
     * QueryParametersParser constructor.
     *
     * @param mixed[] $parameters
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
     * @param mixed[] $parameters
     *
     * @throws JsonApiException
     */
    public function parseParameters(array $parameters): void
    {
        foreach ($parameters as $parameter => $value) {
            if (\in_array($parameter, $this->knownParameters, true)) {
                try {
                    $method = 'parse' . \ucfirst($parameter) . 'Parameter';

                    $this->$method($value);
                } catch (\TypeError $error) {
                    throw new JsonApiException(
                        $this->getJsonApiError(
                            'Invalid parameter',
                            \sprintf('Parameter "%s" has an invalid value', $parameter)
                        ),
                        JsonApiException::HTTP_CODE_BAD_REQUEST,
                        new \Exception($error->getMessage(), $error->getCode(), $error)
                    );
                }
            }
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getFields(): iterable
    {
        return $this->fields;
    }

    /**
     * Set fields.
     *
     * @param string[] $fields
     *
     * @return self
     */
    public function setFields(array $fields): self
    {
        $this->fields = $fields;

        return $this;
    }

    /**
     * Parse fields.
     *
     * @param string[] $fields
     *
     * @throws JsonApiException
     *
     * @return self
     */
    protected function parseFieldsParameter(array $fields): self
    {
        \array_walk(
            $fields,
            function (&$fieldList, $resourceName) {
                if (\is_numeric($resourceName)) {
                    throw new JsonApiException($this->getJsonApiError(
                        'Invalid parameter',
                        \sprintf('Parameter "%s" has an invalid value', static::PARAM_FIELDS)
                    ));
                }

                $fieldList = $this->splitString(static::PARAM_FIELDS, $fieldList, ',');
            }
        );

        $this->fields = $fields;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getIncludes(): iterable
    {
        return $this->includes;
    }

    /**
     * Set includes.
     *
     * @param string[] $includes
     *
     * @return self
     */
    public function setIncludes(array $includes): self
    {
        $this->includes = $includes;

        return $this;
    }

    /**
     * Parse include query parameter.
     *
     * @param string $includes
     *
     * @throws JsonApiException
     *
     * @return self
     */
    public function parseIncludeParameter(string $includes): self
    {
        $this->includes = $this->splitString(static::PARAM_INCLUDE, $includes, ',');

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getSorts(): iterable
    {
        return $this->sorts;
    }

    /**
     * Set sorts.
     *
     * @param bool[] $sorts
     *
     * @return self
     */
    public function setSorts(array $sorts): self
    {
        $this->sorts = $sorts;

        return $this;
    }

    /**
     * Parse sorts query parameter.
     *
     * @param string $sorts
     *
     * @throws JsonApiException
     *
     * @return self
     */
    public function parseSortParameter(string $sorts): self
    {
        $sortList = [];

        foreach ($this->splitString(static::PARAM_SORT, $sorts, ',') as $field) {
            $isAsc = $field[0] !== '-';
            if (\in_array($field[0], ['-', '+'], true)) {
                $field = \substr($field, 1);
            }

            $sortList[$field] = $isAsc;
        }

        $this->sorts = $sortList;

        return $this;
    }

    /**
     * Get query paging.
     *
     * @return mixed[]
     */
    public function getPaging(): array
    {
        return $this->paging;
    }

    /**
     * Set query paging.
     *
     * @param mixed[] $paging
     *
     * @return self
     */
    public function setPaging(array $paging): self
    {
        $this->paging = $paging;

        return $this;
    }

    /**
     * Parse page query parameter.
     *
     * @param mixed[] $paging
     *
     * @throws JsonApiException
     *
     * @return self
     */
    public function parsePageParameter(array $paging): self
    {
        \array_walk(
            $paging,
            function (&$value, $key) {
                if (\is_numeric($key) || !\is_numeric($value) || ($value + 0) !== (int) $value) {
                    throw new JsonApiException($this->getJsonApiError(
                        'Invalid parameter',
                        \sprintf('Parameter %s has an invalid value', static::PARAM_PAGE)
                    ));
                }

                $value = (int) $value;
            }
        );

        $this->paging = $paging;

        return $this;
    }

    /**
     * Get query filters.
     *
     * @return mixed
     */
    public function getFilters()
    {
        return $this->filters;
    }

    /**
     * Set query filters.
     *
     * @param mixed $filters
     *
     * @return self
     */
    public function setFilters($filters): self
    {
        $this->filters = $filters;

        return $this;
    }

    /**
     * Parse filter query parameters.
     *
     * @param mixed $filters
     *
     * @throws JsonApiException
     *
     * @return self
     */
    public function parseFilterParameter($filters): self
    {
        $this->filters = $filters;

        return $this;
    }

    /**
     * @param string $parameter
     * @param string $string
     * @param string $separator
     *
     * @throws JsonApiException
     *
     * @return string[]
     */
    protected function splitString(string $parameter, string $string, string $separator): array
    {
        return \array_filter(\array_map(
            function (string $value) use ($parameter): string {
                $value = \trim($value);

                if ($value === '') {
                    throw new JsonApiException($this->getJsonApiError(
                        'Invalid parameter',
                        \sprintf('Parameter "%s" has an invalid value', $parameter)
                    ));
                }

                return $value;
            },
            \explode($separator, $string)
        ));
    }

    /**
     * Get a JSON API error.
     *
     * @param string $title
     * @param string $detail
     *
     * @return Error
     */
    protected function getJsonApiError(string $title, string $detail): Error
    {
        return new Error(
            null,
            null,
            (string) JsonApiException::HTTP_CODE_BAD_REQUEST,
            null,
            $title,
            $detail
        );
    }
}
