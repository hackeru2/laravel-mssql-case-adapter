# Laravel MSSQL Case Adapter

[![tests](https://github.com/hackeru2/laravel-mssql-case-adapter/actions/workflows/tests.yml/badge.svg)](https://github.com/hackeru2/laravel-mssql-case-adapter/actions/workflows/tests.yml)

Write clean, snake_case Laravel code against legacy **UPPERCASE** SQL Server schemas.

Legacy enterprise MSSQL databases often use strict uppercase naming (`SITES`, `SITE_ID`, `USER_FIRST_NAME`). Writing `$user->USER_FIRST_NAME` ruins the developer experience and fights every Eloquent convention — especially **relationships and eager loading**. This package translates identifiers transparently at the database driver and grammar layer: your models, relations, and queries stay 100% lowercase, and the package converts both directions automatically.

```php
class Site extends Model            // table SITES (SITE_ID, SITE_NAME)
{
    public function user(): HasOne
    {
        return $this->hasOne(User::class, 'site_id');   // column SITE_ID on USERS
    }
}

Site::with('user')->where('site_name', 'HQ')->get();
// select * from [SITES] where [SITE_NAME] = ?
// select * from [USERS] where [USERS].[SITE_ID] in (...)
// → models hydrate with lowercase attributes: $site->site_name, $site->user->user_id
```

## Installation

```bash
composer require basedon/laravel-mssql-case-adapter
```

Point your connection at the `mssql-adapted` driver — everything else is a standard `sqlsrv` config:

```php
// config/database.php
'connections' => [
    'legacy' => [
        'driver'   => 'mssql-adapted',
        'host'     => env('DB_HOST', 'localhost'),
        'database' => env('DB_DATABASE'),
        'username' => env('DB_USERNAME'),
        'password' => env('DB_PASSWORD'),
        // 'trust_server_certificate' => true,
    ],
],
```

That's it. No traits, no per-model configuration.

## How it works

| Layer | Mechanism |
| :--- | :--- |
| Queries out | A custom query grammar translates every table, column, and alias through the identifier resolver before wrapping it in `[...]` — selects, wheres, joins, order by, inserts, updates, and relationship constraints included. |
| Results in | `PDO::ATTR_CASE => PDO::CASE_LOWER` is merged into the connection options (covers raw `DB::select()` too), and the query post-processor maps result keys through the resolver as a safety net. |
| Schema | The schema grammar translates identifiers as well, so `Schema::hasTable('sites')` and migrations work — including under case-sensitive collations. |

> **Do I even need this?** Under SQL Server's default case-insensitive collations, identifier *matching* already works; the pain is result hydration and Eloquent relationship key matching, which this package fixes. Under case-**sensitive** (binary) collations, the grammar translation is what makes lowercase queries work at all. CI runs the full suite against both.

## Custom naming strategies

The default `UppercaseResolver` maps `site_name ↔ SITE_NAME`. For prefixed or irregular legacy schemas (e.g. `orders` → `TBL_ORDERS`), implement the contract and register it:

```php
use Basedon\MssqlCaseAdapter\Resolvers\IdentifierResolver;

class TblPrefixResolver implements IdentifierResolver
{
    public function toDatabase(string $identifier): string
    {
        return 'TBL_'.strtoupper($identifier);
    }

    public function toApplication(string $identifier): string
    {
        return strtolower(preg_replace('/^TBL_/', '', $identifier));
    }
}
```

```php
// Globally, in config/mssql-case-adapter.php (php artisan vendor:publish --tag=mssql-case-adapter-config)
'identifier_resolver' => TblPrefixResolver::class,

// …or per connection:
'legacy' => [
    'driver' => 'mssql-adapted',
    'identifier_resolver' => TblPrefixResolver::class,
    // ...
],
```

The resolver must be bijective for the identifiers you use: `toApplication(toDatabase($x)) === $x`.

## Generating models from a legacy schema

```bash
php artisan mssql:inspect --connection=legacy --path=app/Models --namespace="App\Models"
```

Scans `INFORMATION_SCHEMA` and generates Eloquent model stubs with the right `$connection`, `$table` (only when not derivable), `$primaryKey`, `$casts`, and `$timestamps` — all in lowercase application naming.

## Configuration

| Key | Default | Meaning |
| :--- | :--- | :--- |
| `identifier_resolver` | `UppercaseResolver::class` | Naming strategy (global or per connection). |
| `pdo_case_lower` | `true` | Merge `PDO::ATTR_CASE => CASE_LOWER` into options (per connection: `case_lower`). An explicit `PDO::ATTR_CASE` in the connection `options` always wins. |
| `normalize_results` | `true` | Post-processor maps result keys through the resolver. |

## Limitations

- **Raw SQL bypasses the grammar.** `DB::raw()`, `selectRaw()`, `orderByRaw()`, `DB::statement()` are passed through untouched — write database-side (UPPERCASE) identifiers in raw fragments. Result keys of raw *selects* are still lowercased by the PDO case option.
- **Mixed-case application identifiers** (`siteName`) are not round-trippable with the default resolver; stick to snake_case (Laravel convention) or supply a custom resolver.

## Testing

```bash
composer test        # unit + feature (no database needed)
composer analyse     # larastan, level 5
composer format      # pint
```

Integration tests run in GitHub Actions against a real `mcr.microsoft.com/mssql/server:2022` container with uppercase tables, on both a default-collation database and a `Latin1_General_BIN` (case-sensitive) database.

## License

MIT
