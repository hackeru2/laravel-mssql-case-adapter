<?php

use Illuminate\Support\Facades\DB;
use Tests\Fixtures\Site;
use Tests\Fixtures\User;

beforeEach(function () {
    if (! env('MSSQL_INTEGRATION')) {
        $this->markTestSkipped('Set MSSQL_INTEGRATION=1 with a reachable SQL Server to run integration tests.');
    }

    foreach (['legacy', 'legacy_cs'] as $name) {
        DB::connection($name)->table('users')->delete();
        DB::connection($name)->table('sites')->delete();
    }
});

it('creates and reads models with lowercase attributes against uppercase tables', function () {
    $site = Site::create(['site_name' => 'HQ']);

    expect($site->site_id)->toBeInt()->toBeGreaterThan(0);

    $fresh = Site::query()->first();

    expect($fresh->site_name)->toBe('HQ')
        ->and(array_keys($fresh->toArray()))->toBe(['site_id', 'site_name']);
});

it('lazy loads and eager loads relations across uppercase tables', function () {
    $site = Site::create(['site_name' => 'HQ']);
    $site->users()->create(['user_name' => 'amir']);

    $lazy = Site::query()->first();
    expect($lazy->user->user_name)->toBe('amir');

    $eager = Site::with('users')->get();
    expect($eager->first()->users)->toHaveCount(1)
        ->and($eager->first()->users->first()->site_id)->toBe($site->site_id);

    $inverse = User::with('site')->first();
    expect($inverse->site->site_name)->toBe('HQ');
});

it('updates and deletes through lowercase attributes', function () {
    $site = Site::create(['site_name' => 'HQ']);

    $site->update(['site_name' => 'Headquarters']);
    expect(Site::where('site_name', 'Headquarters')->exists())->toBeTrue();

    $site->delete();
    expect(Site::query()->count())->toBe(0);
});

it('works against a case-sensitive (binary collation) database', function () {
    $site = Site::on('legacy_cs')->create(['site_name' => 'HQ']);
    $site->users()->create(['user_name' => 'amir']);

    $found = Site::on('legacy_cs')->where('site_name', 'HQ')->first();
    expect($found)->not->toBeNull()
        ->and($found->site_name)->toBe('HQ');

    $eager = Site::on('legacy_cs')->with('user')->get();
    expect($eager->first()->user->user_name)->toBe('amir');
});

it('lowercases keys of raw selects via the PDO case option', function () {
    Site::create(['site_name' => 'HQ']);

    $rows = DB::connection('legacy')->select('SELECT SITE_ID, SITE_NAME FROM SITES');

    expect(array_keys((array) $rows[0]))->toBe(['site_id', 'site_name']);
});
