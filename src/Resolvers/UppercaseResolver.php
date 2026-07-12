<?php

namespace Basedon\MssqlCaseAdapter\Resolvers;

class UppercaseResolver implements IdentifierResolver
{
    public function toDatabase(string $identifier): string
    {
        return strtoupper($identifier);
    }

    public function toApplication(string $identifier): string
    {
        return strtolower($identifier);
    }
}
