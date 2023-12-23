<?php

declare(strict_types=1);

$dotenv = new Dotenv\Dotenv(__DIR__);
$dotenv->load();

$destination = getenv('API_DESTINATION');
$server      = getenv('API_SERVER');
$version     = getenv('API_VERSION');

define('ROOT', getenv('API_ROOT'));

$tags = [
    // system
    'about'             => ['description' => 'These endpoints deliver general system information, version- and meta information.', 'api_version' => ['v1']],
    'configuration'     => [
        'description' => 'These endpoints allow you to manage and update the Firefly III configuration. You need to have the "owner" role to update configuration.',
        'api_version' => ['v1'],
    ],
    'users'             => [
        'description' => 'Use these endpoints to manage the users registered within Firefly III. You need to have the "owner" role to access these endpoints.',
        'api_version' => ['v1'],
    ],

    // autocomplete
    'autocomplete'      => [
        'description' => 'Auto-complete endpoints show basic information about Firefly III models, like the name and maybe some amounts. They all support a search query and can be used to autocomplete data in forms. Autocomplete return values always have a "name"-field.',
        'api_version' => ['v1'],
    ],

    // charts
    'charts'            => ['description' => 'The "charts" endpoints deliver optimised data for charts and graphs.', 'api_version' => ['v1', 'v2']],

    // data
    'data'              => ['description' => 'The "data"-endpoints manage generic Firefly III and user-specific data.', 'api_version' => ['v1']],

    // insight
    'insight'           => [
        'description' => 'The "insight" endpoints try to deliver sums, balances and insightful information in the broadest sense of the word.',
        'api_version' => ['v1'],
    ],
    'summary'           => [
        'description' => 'These endpoints deliver summaries, like sums, lists of numbers and other processed information. Mainly used for the main dashboard and pretty specific for Firefly III itself.',
        'api_version' => ['v1'],
    ],

    // search
    'search'            => [
        'description' => 'Endpoints that allow you to search through the user\'s financial data. Different from the autocomplete endpoints, the search accepts more advanced arguments.',
        'api_version' => ['v1'],
    ],

    // user
    'preferences'       => [
        'description' => 'These endpoints can be used to manage the user\'s preferences, including some hidden ones.',
        'api_version' => ['v1', 'v2'],
    ],
    'webhooks'          => [
        'description' => 'These endpoints can be used to manage the user\'s webhooks and triggers them if necessary.',
        'api_version' => ['v1'],
    ],

    // models
    'accounts'          => [
        'description' => 'Endpoints that deliver all of the user\'s asset, expense and other accounts (and the metadata) together with related transactions, piggy banks and other objects. Also delivers endpoints for CRUD operations for accounts.',
        'api_version' => ['v1', 'v2'],
    ],
    'attachments'       => [
        'description' => 'Endpoints to manage the attachments of the authenticated user, including up- and downloading of the files.',
        'api_version' => ['v1'],
    ],
    'available_budgets' => [
        'description' => 'Endpoints to manage the total available amount that the user has made available to themselves. Used in periodic budgeting.',
        'api_version' => ['v1'],
    ],
    'bills'             => ['description' => 'Endpoints to manage a user\'s bills and all related objects.', 'api_version' => ['v1']],
    'budgets'           => [
        'description' => 'Endpoints to manage a user\'s budgets and get info on the related objects, like limits.',
        'api_version' => ['v1', 'v2'],
    ],
    'categories'        => [
        'description' => 'Endpoints to manage a user\'s categories and get information on transactions and other related objects.',
        'api_version' => ['v1'],
    ],
    'object_groups'     => [
        'description' => 'Endpoints to control and manage all of the user\'s object groups. Can only be created in conjunction with another object (for example a piggy bank) and will auto-delete when no other items are linked to it.',
        'api_version' => ['v1'],
    ],
    'piggy_banks'       => [
        'description' => 'Endpoints to control and manage all of the user\'s piggy banks and related objects and information.',
        'api_version' => ['v1'],
    ],
    'recurrences'       => [
        'description' => 'Use these endpoints to manage the user\'s recurring transactions, trigger the creation of transactions and manage the settings.',
        'api_version' => ['v1'],
    ],
    'rules'             => [
        'description' => 'These endpoints can be used to manage all of the user\'s rules. Also includes triggers to execute or test rules and individual triggers.',
        'api_version' => ['v1'],
    ],
    'rule_groups'       => ['description' => 'Manage all of the user\'s groups of rules and trigger the execution of entire groups.', 'api_version' => ['v1']],
    'tags'              => ['description' => 'This endpoint manages all of the user\'s tags.', 'api_version' => ['v1']],
    'transactions'      => [
        'description' => 'The most-used endpoints in Firefly III, these endpoints are used to manage the user\'s transactions.',
        'api_version' => ['v1'],
    ],
    'currencies'        => [
        'description' => 'Endpoints to manage the currencies in Firefly III. Depending on the user\'s role you can also disable and enable them, or add new ones.',
        'api_version' => ['v1'],
    ],
    'links'             => [
        'description' => 'Endpoints to manage links between transactions, and manage the type of links available.',
        'api_version' => ['v1'],
    ],

    // v2 net worth
    'net-worth'         => ['description' => 'Shows you the net worth of the current user.', 'api_version' => ['v2']],

    // v2 tags
    'transactions-sum'  => ['description' => 'Endpoints to sum transactions based on various properties.', 'api_version' => ['v2']],
];
ksort($tags);

// scan directories and add all paths:
$directories = [
    [
        'path'        => 'yaml/v1/paths',
        'identifier'  => 'paths',
        'indentation' => 1,
        'api_version' => ['v1'],
    ],
    [
        'path'        => 'yaml/v1/schemas',
        'identifier'  => 'schemas',
        'indentation' => 2,
        'api_version' => ['v1'],
    ],
    [
        'path'        => 'yaml/v2/paths',
        'identifier'  => 'paths',
        'indentation' => 1,
        'api_version' => ['v2'],
    ],
    [
        'path'        => 'yaml/v2/schemas',
        'identifier'  => 'schemas',
        'indentation' => 2,
        'api_version' => ['v2'],
    ],
    [
        'path'        => 'yaml/shared/arrays',
        'identifier'  => 'schemas',
        'indentation' => 2,
        'api_version' => ['v1', 'v2'],
    ],
    [
        'path'        => 'yaml/shared/lists',
        'identifier'  => 'schemas',
        'indentation' => 2,
        'api_version' => ['v1', 'v2'],
    ],
    [
        'path'        => 'yaml/shared/models',
        'identifier'  => 'schemas',
        'indentation' => 2,
        'api_version' => ['v1', 'v2'],
    ],
    [
        'path'        => 'yaml/shared/schemas',
        'identifier'  => 'schemas',
        'indentation' => 2,
        'api_version' => ['v1', 'v2'],
    ],
    [
        'path'        => 'yaml/shared/properties',
        'identifier'  => 'schemas',
        'indentation' => 2,
        'api_version' => ['v1', 'v2'],
    ],
    [
        'path'        => 'yaml/shared/filters',
        'identifier'  => 'schemas',
        'indentation' => 2,
        'api_version' => ['v1', 'v2'],
    ],
];
