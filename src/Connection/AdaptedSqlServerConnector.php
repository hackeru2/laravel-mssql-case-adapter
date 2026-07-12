<?php

namespace Basedon\MssqlCaseAdapter\Connection;

use Illuminate\Database\Connectors\SqlServerConnector;
use PDO;

class AdaptedSqlServerConnector extends SqlServerConnector
{
    public function __construct(protected bool $defaultCaseLower = true)
    {
    }

    /**
     * Merge PDO::ATTR_CASE => PDO::CASE_LOWER into the connection options
     * so fetched column keys are lowercased at the driver level. An
     * explicit PDO::ATTR_CASE in the connection's 'options' array wins.
     */
    public function getOptions(array $config)
    {
        $options = parent::getOptions($config);

        $explicitlySet = array_key_exists(PDO::ATTR_CASE, $config['options'] ?? []);

        if (! $explicitlySet && ($config['case_lower'] ?? $this->defaultCaseLower)) {
            $options[PDO::ATTR_CASE] = PDO::CASE_LOWER;
        }

        return $options;
    }
}
