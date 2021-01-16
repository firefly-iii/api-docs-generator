<?php
declare(strict_types=1);

$dotenv = new Dotenv\Dotenv(__DIR__);
$dotenv->load();

$version     = getenv('API_VERSION');
$destination = getenv('API_DESTINATION');

define('ROOT', getenv('API_ROOT'));

$tags = [
    'about'                   => 'General system information, versions, and the currently logged in user.',
    'accounts'                => 'All asset, expense and other accounts (and the metadata) together with related transactions, piggy banks and other objects.',
    'attachments'             => 'All attachments of the authenticated user, including up- and downloading of the files.',
    'autocomplete'            => 'All auto-complete endpoints.',
    'available_budgets'       => 'The total available amount that is available for budgeting every period.',
    'bills'                   => 'All bills by the user.',
    'budgets'                 => 'Manage all the user\'s budgets',
    'charts'                  => 'This endpoint delivers optimised data for charts and graphics.',
    'categories'              => 'Manage all the user\'s categories',
    'configuration'           => 'Manage the global Firefly III configuration',
    'currencies'              => 'Manage all currencies in the system, disable and enable them or add new ones.',
    'import'                  => 'Manage and run imports.',
    'links'                   => 'Manage links between transactions, and manage the type of links available.',
    'piggy_banks'             => 'Control all of the user\'s piggy banks, including money management',
    'preferences'             => 'Manage the user\'s preferences, including some hidden ones.',
    'recurrences'             => 'Manage the user\'s recurring transactions, trigger the creation of transactions and manage the settings.',
    'rules'                   => 'Manage all of the user\'s rules and trigger the execution of rules.',
    'rule_groups'             => 'Manage all of the user\'s groups of rules and trigger the execution of entire groups.',
    'search'                  => 'Search through the user\'s financial data.',
    'summary'                 => 'Endpoint for sums, lists of numbers and other processed information.',
    'tags'                    => 'Manage all the user\'s tags.',
    'transactions'            => 'Manage all the user\'s transactions.',
    'users'                   => 'Manage the users registered within Firefly III.',
    'webhooks'                => 'Manage the user\'s webhooks.',
];

