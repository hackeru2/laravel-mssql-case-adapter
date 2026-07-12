<?php

namespace Basedon\MssqlCaseAdapter\Connection;

use Basedon\MssqlCaseAdapter\Grammar\AdaptedSqlServerQueryGrammar;
use Basedon\MssqlCaseAdapter\Grammar\AdaptedSqlServerSchemaGrammar;
use Basedon\MssqlCaseAdapter\Processor\AdaptedSqlServerProcessor;
use Basedon\MssqlCaseAdapter\Resolvers\IdentifierResolver;
use Basedon\MssqlCaseAdapter\Resolvers\UppercaseResolver;
use Illuminate\Database\SqlServerConnection;

class AdaptedSqlServerConnection extends SqlServerConnection
{
    protected ?IdentifierResolver $identifierResolver = null;

    public function getIdentifierResolver(): IdentifierResolver
    {
        if ($this->identifierResolver === null) {
            /** @var class-string<\Basedon\MssqlCaseAdapter\Resolvers\IdentifierResolver>|\Basedon\MssqlCaseAdapter\Resolvers\IdentifierResolver $resolver */
            $resolver = $this->getConfig('identifier_resolver') ?? UppercaseResolver::class;

            $this->identifierResolver = $resolver instanceof IdentifierResolver
                ? $resolver
                : new $resolver;
        }

        return $this->identifierResolver;
    }

    /**
     * @return \Basedon\MssqlCaseAdapter\Grammar\AdaptedSqlServerQueryGrammar
     */
    protected function getDefaultQueryGrammar()
    {
        return new AdaptedSqlServerQueryGrammar($this, $this->getIdentifierResolver());
    }

    /**
     * @return \Basedon\MssqlCaseAdapter\Grammar\AdaptedSqlServerSchemaGrammar
     */
    protected function getDefaultSchemaGrammar()
    {
        return new AdaptedSqlServerSchemaGrammar($this, $this->getIdentifierResolver());
    }

    /**
     * @return \Basedon\MssqlCaseAdapter\Processor\AdaptedSqlServerProcessor
     */
    protected function getDefaultPostProcessor()
    {
        return new AdaptedSqlServerProcessor(
            $this->getIdentifierResolver(),
            (bool) ($this->getConfig('normalize_results') ?? true),
        );
    }
}
