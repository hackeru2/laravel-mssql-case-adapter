<?php

namespace Basedon\MssqlCaseAdapter\Commands;

use Basedon\MssqlCaseAdapter\Connection\AdaptedSqlServerConnection;
use Basedon\MssqlCaseAdapter\MssqlCaseAdapterServiceProvider;
use Illuminate\Console\Command;
use Illuminate\Database\DatabaseManager;
use Illuminate\Support\Str;

class InspectCommand extends Command
{
    protected $signature = 'mssql:inspect
        {--connection= : The adapted connection to inspect (defaults to the default connection)}
        {--path= : Output directory for generated models (defaults to app/Models)}
        {--namespace=App\\Models : Namespace for generated models}
        {--force : Overwrite existing model files}';

    protected $description = 'Generate Eloquent model stubs from a legacy UPPERCASE SQL Server schema';

    public function handle(DatabaseManager $db): int
    {
        $name = $this->option('connection');
        $connection = $db->connection(is_string($name) && $name !== '' ? $name : null);

        if (! $connection instanceof AdaptedSqlServerConnection) {
            $this->error(sprintf(
                "Connection [%s] does not use the '%s' driver.",
                $connection->getName(),
                MssqlCaseAdapterServiceProvider::DRIVER,
            ));

            return self::FAILURE;
        }

        $resolver = $connection->getIdentifierResolver();
        $schema = $connection->getSchemaBuilder();

        $option = $this->option('path');
        $path = is_string($option) && $option !== '' ? $option : app_path('Models');

        if (! is_dir($path)) {
            mkdir($path, 0755, true);
        }

        foreach ($schema->getTables() as $table) {
            $appTable = $resolver->toApplication($table['name']);
            $class = Str::studly(Str::singular($appTable));
            $file = $path.'/'.$class.'.php';

            if (file_exists($file) && ! $this->option('force')) {
                $this->warn("Skipping {$class}: {$file} already exists (use --force to overwrite).");

                continue;
            }

            file_put_contents($file, $this->buildModel(
                $connection, $class, $appTable, $schema->getColumns($appTable), $schema->getIndexes($appTable),
            ));

            $this->info("Generated {$class} for table {$table['name']}.");
        }

        return self::SUCCESS;
    }

    /**
     * @param  array<int, array<string, mixed>>  $columns
     * @param  array<int, array<string, mixed>>  $indexes
     */
    protected function buildModel(
        AdaptedSqlServerConnection $connection,
        string $class,
        string $appTable,
        array $columns,
        array $indexes,
    ): string {
        $resolver = $connection->getIdentifierResolver();

        $attributes = array_map(
            fn (array $column) => $resolver->toApplication($column['name']),
            $columns,
        );

        $primaryKey = 'id';

        foreach ($indexes as $index) {
            if (($index['primary'] ?? false) && count($index['columns']) === 1) {
                $primaryKey = $resolver->toApplication($index['columns'][0]);
            }
        }

        $casts = [];

        foreach ($columns as $column) {
            $cast = match (strtolower((string) $column['type_name'])) {
                'bit' => 'boolean',
                'int', 'bigint', 'smallint', 'tinyint' => 'integer',
                'float', 'real' => 'float',
                'date', 'datetime', 'datetime2', 'smalldatetime' => 'datetime',
                default => null,
            };

            if ($cast !== null) {
                $casts[$resolver->toApplication($column['name'])] = $cast;
            }
        }

        $timestamps = in_array('created_at', $attributes, true) && in_array('updated_at', $attributes, true);
        $guessedTable = Str::snake(Str::pluralStudly($class));

        $properties = [
            sprintf("    protected \$connection = '%s';", $connection->getName()),
        ];

        if ($guessedTable !== $appTable) {
            $properties[] = sprintf("    protected \$table = '%s';", $appTable);
        }

        if ($primaryKey !== 'id') {
            $properties[] = sprintf("    protected \$primaryKey = '%s';", $primaryKey);
        }

        if (! $timestamps) {
            $properties[] = '    public $timestamps = false;';
        }

        if ($casts !== []) {
            $castLines = implode("\n", array_map(
                fn (string $attribute, string $cast) => sprintf("        '%s' => '%s',", $attribute, $cast),
                array_keys($casts),
                $casts,
            ));

            $properties[] = "    protected \$casts = [\n{$castLines}\n    ];";
        }

        $body = implode("\n\n", $properties);
        $namespace = trim((string) $this->option('namespace'), '\\');

        return <<<PHP
<?php

namespace {$namespace};

use Illuminate\Database\Eloquent\Model;

class {$class} extends Model
{
{$body}
}

PHP;
    }
}
