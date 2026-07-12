<?php

use Basedon\MssqlCaseAdapter\Resolvers\UppercaseResolver;

it('translates application identifiers to uppercase database identifiers', function () {
    $resolver = new UppercaseResolver;

    expect($resolver->toDatabase('sites'))->toBe('SITES')
        ->and($resolver->toDatabase('site_name'))->toBe('SITE_NAME')
        ->and($resolver->toDatabase('SITE_NAME'))->toBe('SITE_NAME');
});

it('translates database identifiers to lowercase application identifiers', function () {
    $resolver = new UppercaseResolver;

    expect($resolver->toApplication('SITES'))->toBe('sites')
        ->and($resolver->toApplication('SITE_NAME'))->toBe('site_name')
        ->and($resolver->toApplication('site_name'))->toBe('site_name');
});
