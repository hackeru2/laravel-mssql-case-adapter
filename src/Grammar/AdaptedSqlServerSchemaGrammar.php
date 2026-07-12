<?php

namespace Basedon\MssqlCaseAdapter\Grammar;

use Basedon\MssqlCaseAdapter\Connection\AdaptedSqlServerConnection;
use Basedon\MssqlCaseAdapter\Resolvers\IdentifierResolver;
use Illuminate\Database\Schema\Grammars\SqlServerGrammar;

class AdaptedSqlServerSchemaGrammar extends SqlServerGrammar
{
    public function __construct(
        AdaptedSqlServerConnection $connection,
        protected IdentifierResolver $resolver,
    ) {
        parent::__construct($connection);
    }

    /**
     * Wrap a single string in keyword identifiers, translating the
     * application-side name to its database-side form first.
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

    /**
     * The following compile methods embed the table name as a string value
     * (not a wrapped identifier), so it is compared against the catalog
     * using the database collation. Resolve it to the database-side form
     * so Schema::hasTable('sites') matches a legacy SITES table even under
     * case-sensitive collations.
     */
    public function compileTableExists($schema, $table)
    {
        return parent::compileTableExists($schema, $this->resolver->toDatabase($table));
    }

    public function compileColumns($schema, $table)
    {
        return parent::compileColumns($schema, $this->resolver->toDatabase($table));
    }

    public function compileIndexes($schema, $table)
    {
        return parent::compileIndexes($schema, $this->resolver->toDatabase($table));
    }

    public function compileForeignKeys($schema, $table)
    {
        return parent::compileForeignKeys($schema, $this->resolver->toDatabase($table));
    }
}
