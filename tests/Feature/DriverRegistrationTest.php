<?php

use Basedon\MssqlCaseAdapter\Connection\AdaptedSqlServerConnection;
use Basedon\MssqlCaseAdapter\Grammar\AdaptedSqlServerQueryGrammar;
use Basedon\MssqlCaseAdapter\Grammar\AdaptedSqlServerSchemaGrammar;
use Basedon\MssqlCaseAdapter\Processor\AdaptedSqlServerProcessor;
use Basedon\MssqlCaseAdapter\Resolvers\UppercaseResolver;
use Illuminate\Support\Facades\DB;
use Tests\Fixtures\Site;

it('resolves the mssql-adapted driver to the adapted connection', function () {
    $connection = DB::connection('legacy');

    $connection->getSchemaBuilder(); // initializes the schema grammar

    expect($connection)->toBeInstanceOf(AdaptedSqlServerConnection::class)
        ->and($connection->getQueryGrammar())->toBeInstanceOf(AdaptedSqlServerQueryGrammar::class)
        ->and($connection->getPostProcessor())->toBeInstanceOf(AdaptedSqlServerProcessor::class)
        ->and($connection->getSchemaGrammar())->toBeInstanceOf(AdaptedSqlServerSchemaGrammar::class)
        ->and($connection->getIdentifierResolver())->toBeInstanceOf(UppercaseResolver::class);
});

it('compiles eloquent queries through the container-managed connection', function () {
    expect(Site::query()->toSql())->toBe('select * from [SITES]');
});

it('merges package defaults into the connection config', function () {
    $connection = DB::connection('legacy');

    expect($connection->getConfig('identifier_resolver'))->toBe(UppercaseResolver::class)
        ->and($connection->getConfig('normalize_results'))->toBeTrue();
});
