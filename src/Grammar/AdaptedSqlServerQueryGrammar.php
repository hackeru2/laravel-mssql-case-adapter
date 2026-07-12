<?php

namespace Basedon\MssqlCaseAdapter\Grammar;

use Basedon\MssqlCaseAdapter\Connection\AdaptedSqlServerConnection;
use Basedon\MssqlCaseAdapter\Resolvers\IdentifierResolver;
use Illuminate\Database\Query\Grammars\SqlServerGrammar;

class AdaptedSqlServerQueryGrammar extends SqlServerGrammar
{
    public function __construct(
        AdaptedSqlServerConnection $connection,
        protected IdentifierResolver $resolver,
    ) {
        parent::__construct($connection);
    }

    /**
     * Wrap a single string in keyword identifiers, translating the
     * application-side name to its database-side form first. Every table,
     * column, and alias funnels through here; Expression instances are
     * filtered out earlier in wrap() and never reach this method.
     *
     * @param  string  $value
     * @return string
     */
    protected function wrapValue($value)
    {
        if ($value !== '*') {
            $value = $this->resolver->toDatabase($value);
        }

        return parent::wrapValue($value);
    }
}
