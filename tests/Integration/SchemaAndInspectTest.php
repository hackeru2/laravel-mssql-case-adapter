<?php

use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Schema;

beforeEach(function () {
    if (! env('MSSQL_INTEGRATION')) {
        $this->markTestSkipped('Set MSSQL_INTEGRATION=1 with a reachable SQL Server to run integration tests.');
    }
});

it('finds uppercase tables through the schema builder', function () {
    expect(Schema::connection('legacy')->hasTable('sites'))->toBeTrue()
        ->and(Schema::connection('legacy')->hasColumn('sites', 'site_name'))->toBeTrue()
        ->and(Schema::connection('legacy')->hasTable('missing'))->toBeFalse();
});

it('finds uppercase tables under a case-sensitive collation', function () {
    expect(Schema::connection('legacy_cs')->hasTable('sites'))->toBeTrue();
});

it('generates lowercase model stubs from the legacy schema', function () {
    $path = sys_get_temp_dir().'/mssql-inspect-'.uniqid();

    $this->artisan('mssql:inspect', ['--connection' => 'legacy', '--path' => $path])
        ->assertSuccessful();

    $model = $path.'/Site.php';

    expect(file_exists($model))->toBeTrue();

    $contents = file_get_contents($model);

    expect($contents)->toContain("protected \$connection = 'legacy';")
        ->toContain("protected \$primaryKey = 'site_id';")
        ->toContain("'site_id' => 'integer'")
        ->toContain('public $timestamps = false;');

    File::deleteDirectory($path);
});
