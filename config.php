<?php
declare(strict_types=1);

$version     = '0.0.1-test';
$destination = '/sites/FF3/api-docs';

define('ROOT', '/sites/projects/api-doc-generator');

$tags = [
    'about'                   => 'General system information, versions, and the currently logged in user.',
    'accounts'                => 'All asset, expense and other accounts (and the metadata) together with related transactions, piggy banks and other objects.',
    'attachments'             => 'All attachments of the authenticated user, including up- and downloading of the files.',
    'available_budgets'       => 'The total available amount that is available for budgeting every period.',
    'bills'                   => 'All bills by the user.',
    'budgets'                 => 'Manage all the user\'s budgets',
    'categories'              => 'Manage all the user\'s categories',
    'currency_exchange_rates' => 'Manage system currency exchange rates, add your own rates or read them from providers configured in Firefly III',
    'configuration'           => 'Manage the global Firefly III configuration',
    'currencies'              => 'Manage all currencies in the system, disable and enable them or add new ones.',
    'export'                  => 'Manage and run exports.',
    'import'                  => 'Manage and run imports.',
    'links'                   => 'Manage links between transactions, and manage the type of links available.',
    'piggy_banks'             => 'Control all of the user\'s piggy banks, including money management',
    'preferences'             => 'Manage the user\'s preferences, including some hidden ones.',
    'recurrences'             => 'Manage the user\'s recurring transactions, trigger the creation of transactions and manage the settings.',
    'rules'                   => 'Manage all of the user\'s rules and trigger the execution of rules.',
    'rule_groups'             => 'Manage all of the user\'s groups of rules and trigger the execution of entire groups.',
    'transactions'            => 'Manage all the user\'s transactions.',
    'search'                  => 'Search through the user\'s financial data.',
    'tags'                    => 'Manage all the user\'s tags.',
    'users'                   => 'Manage the users registered within Firefly III.',
];

