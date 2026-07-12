<?php

namespace Basedon\MssqlCaseAdapter\Resolvers;

interface IdentifierResolver
{
    /**
     * Translate an application-side identifier to its database-side form.
     *
     * e.g. 'first_name' => 'FIRST_NAME', 'sites' => 'SITES'
     */
    public function toDatabase(string $identifier): string;

    /**
     * Translate a database-side identifier to its application-side form.
     *
     * e.g. 'FIRST_NAME' => 'first_name', 'SITES' => 'sites'
     */
    public function toApplication(string $identifier): string;
}
