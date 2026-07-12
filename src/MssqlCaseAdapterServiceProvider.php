<?php

namespace Basedon\MssqlCaseAdapter;

use Basedon\MssqlCaseAdapter\Commands\InspectCommand;
use Basedon\MssqlCaseAdapter\Connection\AdaptedSqlServerConnection;
use Basedon\MssqlCaseAdapter\Connection\AdaptedSqlServerConnector;
use Basedon\MssqlCaseAdapter\Resolvers\UppercaseResolver;
use Illuminate\Database\Connection;
use Illuminate\Support\ServiceProvider;

class MssqlCaseAdapterServiceProvider extends ServiceProvider
{
    public const DRIVER = 'mssql-adapted';

    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__.'/../config/mssql-case-adapter.php', 'mssql-case-adapter');

        $this->app->bind('db.connector.'.self::DRIVER, function ($app) {
            return new AdaptedSqlServerConnector(
                (bool) $app['config']->get('mssql-case-adapter.pdo_case_lower', true),
            );
        });

        Connection::resolverFor(self::DRIVER, function ($connection, $database, $prefix, $config) {
            $config += [
                'identifier_resolver' => $this->app['config']->get(
                    'mssql-case-adapter.identifier_resolver', UppercaseResolver::class,
                ),
                'normalize_results' => $this->app['config']->get(
                    'mssql-case-adapter.normalize_results', true,
                ),
            ];

            return new AdaptedSqlServerConnection($connection, $database, $prefix, $config);
        });
    }

    public function boot(): void
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__.'/../config/mssql-case-adapter.php' => config_path('mssql-case-adapter.php'),
            ], 'mssql-case-adapter-config');

            $this->commands([InspectCommand::class]);
        }
    }
}
