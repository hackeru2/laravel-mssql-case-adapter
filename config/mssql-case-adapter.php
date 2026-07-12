<?php

use Basedon\MssqlCaseAdapter\Resolvers\UppercaseResolver;

return [

    /*
    |--------------------------------------------------------------------------
    | Identifier Resolver
    |--------------------------------------------------------------------------
    |
    | The class translating identifiers between the application convention
    | and the database convention. It must implement the IdentifierResolver
    | contract. Override per connection with the 'identifier_resolver' key
    | on the connection configuration array.
    |
    */

    'identifier_resolver' => UppercaseResolver::class,

    /*
    |--------------------------------------------------------------------------
    | PDO Case Lowering
    |--------------------------------------------------------------------------
    |
    | When enabled, PDO::ATTR_CASE => PDO::CASE_LOWER is merged into the
    | connection options so every fetched column key is lowercased at the
    | driver level (covers raw DB::select() calls as well). Override per
    | connection with the 'case_lower' key. An explicit PDO::ATTR_CASE in
    | the connection's 'options' array always wins.
    |
    */

    'pdo_case_lower' => true,

    /*
    |--------------------------------------------------------------------------
    | Result Normalization
    |--------------------------------------------------------------------------
    |
    | When enabled, the query post-processor maps every result key through
    | the resolver's toApplication() method. This is a safety net for
    | custom resolvers that are not a pure case flip, and for setups where
    | PDO case lowering is disabled. Override per connection with the
    | 'normalize_results' key.
    |
    */

    'normalize_results' => true,

];
