<?php

it('uppercases table names', function () {
    $sql = adaptedConnection()->query()->from('sites')->toSql();

    expect($sql)->toBe('select * from [SITES]');
});

it('uppercases where and order by columns', function () {
    $sql = adaptedConnection()->query()
        ->from('sites')
        ->where('site_name', 'HQ')
        ->orderBy('site_id')
        ->toSql();

    expect($sql)->toBe('select * from [SITES] where [SITE_NAME] = ? order by [SITE_ID] asc');
});

it('uppercases qualified columns in joins and preserves alias round-tripping', function () {
    $sql = adaptedConnection()->query()
        ->from('sites')
        ->join('users', 'users.site_id', '=', 'sites.site_id')
        ->select('sites.site_name as name')
        ->toSql();

    expect($sql)->toBe(
        'select [SITES].[SITE_NAME] as [NAME] from [SITES] '
        .'inner join [USERS] on [USERS].[SITE_ID] = [SITES].[SITE_ID]'
    );
});

it('uppercases insert columns', function () {
    $query = adaptedConnection()->query()->from('sites');

    $sql = $query->getGrammar()->compileInsert($query, [['site_name' => 'HQ']]);

    expect($sql)->toBe('insert into [SITES] ([SITE_NAME]) values (?)');
});

it('uppercases update columns', function () {
    $query = adaptedConnection()->query()->from('sites')->where('site_id', 1);

    $sql = $query->getGrammar()->compileUpdate($query, ['site_name' => 'HQ']);

    expect($sql)->toBe('update [SITES] set [SITE_NAME] = ? where [SITE_ID] = ?');
});

it('leaves raw expressions untouched', function () {
    $connection = adaptedConnection();

    $sql = $connection->query()
        ->from('sites')
        ->select($connection->raw('count(*) as aggregate'))
        ->toSql();

    expect($sql)->toBe('select count(*) as aggregate from [SITES]');
});
