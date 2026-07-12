<?php

use Basedon\MssqlCaseAdapter\Processor\AdaptedSqlServerProcessor;
use Basedon\MssqlCaseAdapter\Resolvers\UppercaseResolver;

it('normalizes uppercase result keys to lowercase', function () {
    $processor = new AdaptedSqlServerProcessor(new UppercaseResolver);

    $results = $processor->processSelect(adaptedConnection()->query()->from('sites'), [
        (object) ['SITE_ID' => 1, 'SITE_NAME' => 'HQ'],
        ['SITE_ID' => 2, 'SITE_NAME' => 'Branch'],
    ]);

    expect($results[0])->toBeObject()
        ->and(get_object_vars($results[0]))->toBe(['site_id' => 1, 'site_name' => 'HQ'])
        ->and($results[1])->toBe(['site_id' => 2, 'site_name' => 'Branch']);
});

it('leaves results untouched when normalization is disabled', function () {
    $processor = new AdaptedSqlServerProcessor(new UppercaseResolver, normalizeResults: false);

    $row = (object) ['SITE_ID' => 1];

    $results = $processor->processSelect(adaptedConnection()->query()->from('sites'), [$row]);

    expect($results[0])->toBe($row);
});
