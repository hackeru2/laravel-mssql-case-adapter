<?php

namespace Tests;

use Basedon\MssqlCaseAdapter\MssqlCaseAdapterServiceProvider;
use Orchestra\Testbench\TestCase as Orchestra;

abstract class TestCase extends Orchestra
{
    protected function getPackageProviders($app)
    {
        return [MssqlCaseAdapterServiceProvider::class];
    }

    protected function defineEnvironment($app)
    {
        $shared = [
            'driver' => 'mssql-adapted',
            'host' => env('DB_MSSQL_HOST', '127.0.0.1'),
            'port' => env('DB_MSSQL_PORT', '1433'),
            'username' => env('DB_MSSQL_USERNAME', 'sa'),
            'password' => env('DB_MSSQL_PASSWORD', ''),
            'trust_server_certificate' => true,
        ];

        $app['config']->set('database.connections.legacy', $shared + [
            'database' => env('DB_MSSQL_DATABASE', 'LEGACY_CI'),
        ]);

        $app['config']->set('database.connections.legacy_cs', $shared + [
            'database' => env('DB_MSSQL_DATABASE_CS', 'LEGACY_CS'),
        ]);
    }
}
