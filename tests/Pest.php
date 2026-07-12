<?php

use Basedon\MssqlCaseAdapter\Connection\AdaptedSqlServerConnection;
use Tests\TestCase;

uses(TestCase::class)->in('Feature', 'Integration');

/**
 * A connection wired with the adapted grammar/processor whose PDO closure
 * throws, guaranteeing unit tests never open a real database connection.
 */
function adaptedConnection(): AdaptedSqlServerConnection
{
    return new AdaptedSqlServerConnection(function (): never {
        throw new RuntimeException('Unit tests must not open a database connection.');
    }, 'legacy', '', [
        'driver' => 'mssql-adapted',
        'name' => 'legacy',
        'database' => 'legacy',
    ]);
}
