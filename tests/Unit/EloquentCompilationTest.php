<?php

use Illuminate\Database\ConnectionResolver;
use Illuminate\Database\Eloquent\Model;
use Tests\Fixtures\Site;
use Tests\Fixtures\User;

beforeEach(function () {
    $resolver = new ConnectionResolver(['legacy' => adaptedConnection()]);
    $resolver->setDefaultConnection('legacy');

    Model::setConnectionResolver($resolver);
});

afterEach(function () {
    Model::unsetConnectionResolver();
});

it('compiles the model table to uppercase', function () {
    expect(Site::query()->toSql())->toBe('select * from [SITES]');
});

it('compiles hasOne relation constraints to uppercase', function () {
    $site = (new Site)->forceFill(['site_id' => 7]);

    expect($site->user()->toSql())->toBe(
        'select * from [USERS] where [USERS].[SITE_ID] = ? and [USERS].[SITE_ID] is not null'
    );
});

it('compiles hasMany relation constraints to uppercase', function () {
    $site = (new Site)->forceFill(['site_id' => 7]);

    expect($site->users()->toSql())->toBe(
        'select * from [USERS] where [USERS].[SITE_ID] = ? and [USERS].[SITE_ID] is not null'
    );
});

it('compiles belongsTo relation constraints to uppercase', function () {
    $user = (new User)->forceFill(['user_id' => 3, 'site_id' => 7]);

    expect($user->site()->toSql())->toBe(
        'select * from [SITES] where [SITES].[SITE_ID] = ?'
    );
});

it('compiles model where clauses to uppercase', function () {
    expect(Site::where('site_name', 'HQ')->toSql())->toBe(
        'select * from [SITES] where [SITE_NAME] = ?'
    );
});
