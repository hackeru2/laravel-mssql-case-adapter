<?php

namespace Basedon\MssqlCaseAdapter\Processor;

use Basedon\MssqlCaseAdapter\Resolvers\IdentifierResolver;
use Illuminate\Database\Query\Builder;
use Illuminate\Database\Query\Processors\SqlServerProcessor;
use stdClass;

class AdaptedSqlServerProcessor extends SqlServerProcessor
{
    public function __construct(
        protected IdentifierResolver $resolver,
        protected bool $normalizeResults = true,
    ) {
    }

    /**
     * Map every result key to its application-side form so hydrated models
     * expose lowercase attributes even when the driver returns the legacy
     * UPPERCASE column names.
     *
     * @param  \Illuminate\Database\Query\Builder  $query
     * @param  array  $results
     * @return array
     */
    public function processSelect(Builder $query, $results)
    {
        $results = parent::processSelect($query, $results);

        if (! $this->normalizeResults) {
            return $results;
        }

        return array_map($this->normalizeRow(...), $results);
    }

    /**
     * @param  mixed  $row
     * @return mixed
     */
    protected function normalizeRow($row)
    {
        if (is_object($row)) {
            $normalized = new stdClass;

            foreach (get_object_vars($row) as $key => $value) {
                $normalized->{$this->resolver->toApplication($key)} = $value;
            }

            return $normalized;
        }

        if (is_array($row)) {
            $normalized = [];

            foreach ($row as $key => $value) {
                $normalized[is_string($key) ? $this->resolver->toApplication($key) : $key] = $value;
            }

            return $normalized;
        }

        return $row;
    }
}
