<?php
declare(strict_types=1);

$dotenv = new Dotenv\Dotenv(__DIR__);
$dotenv->load();

$version     = '1.5.0';
$destination = getenv('API_DESTINATION');
$server      = getenv('API_SERVER');

define('ROOT', getenv('API_ROOT'));

$tags = [
    // autocomplete
    'autocomplete'      => ['description' => 'Auto-complete endpoints show basic information about Firefly III models, like the name and maybe some amounts. They all support a search query and can be used to autocomplete data in forms. Autocomplete return values always have a "name"-field.',],

    // charts
    'charts'            => ['description' => 'The "charts" endpoints deliver optimised data for charts and graphs.',],

    // data
    'data'              => ['description' => 'The "data"-endpoints deliver generic Firefly III and user-specific data.',],

    // insight
    'insight'           => ['description' => 'The "insight" endpoints try to deliver sums, balances and insightful information in the broadest sense of the word.'],
    'summary'           => ['description' => 'These endpoints deliver summaries, like sums, lists of numbers and other processed information. Mainly used for the main dashboard and pretty specific for Firefly III itself.'],

    // models
    'accounts'          => ['description' => 'Endpoints that deliver all of the user\'s asset, expense and other accounts (and the metadata) together with related transactions, piggy banks and other objects. Also delivers endpoints for CRUD operations for accounts.'],
    'attachments'       => ['description' => 'Endpoints to manage the attachments of the authenticated user, including up- and downloading of the files.'],
    'available_budgets' => ['description' => 'Endpoints to manage the total available amount that the user has made available to themselves. Used in periodic budgeting.'],
    'bills'             => ['description' => 'Endpoints to manage a user\'s bills and all related objects.'],
    'budgets'           => ['description' => 'Endpoints to manage a user\'s budgets and get info on the related objects, like limits.'],
    'categories'        => ['description' => 'Endpoints to manage a user\'s categories and get information on transactions and other related objects.'],
    'object_groups'     => ['description' => 'Endpoints to control and manage all of the user\'s object groups. Can only be created in conjunction with another object (for example a piggy bank) and will auto-delete when no other items are linked to it.'],
    'piggy_banks'       => ['description' => 'Endpoints to control and manage all of the user\'s piggy banks and related objects and information.'],
    'recurrences'       => ['description' => 'Use these endpoints to manage the user\'s recurring transactions, trigger the creation of transactions and manage the settings.'],
    'rules'             => ['description' => 'These endpoints can be used to manage all of the user\'s rules. Also includes triggers to execute or test rules and individual triggers.'],
    'rule_groups'       => ['description' => 'Manage all of the user\'s groups of rules and trigger the execution of entire groups.'],
    'tags'              => ['description' => 'This endpoint manages all of the user\'s tags.'],
    'transactions'      => ['description' => 'The most-used endpoints in Firefly III, these endpoints are used to manage the user\'s transactions.'],
    'currencies'        => ['description' => 'Endpoints to manage the currencies in Firefly III. Depending on the user\'s role you can also disable and enable them, or add new ones.'],
    'links'             => ['description' => 'Endpoints to manage links between transactions, and manage the type of links available.'],

    // search
    'search'            => ['description' => 'Endpoints that allow you to search through the user\'s financial data. Different from the autocomplete endpoints, the search accepts more advanced arguments.'],

    // system
    'about'             => ['description' => 'These endpoints deliver general system information, version- and meta information.'],
    //    'configuration'     => ['description' => 'These endpoints allow you to manage and update the Firefly III configuration'],
    //    'users'             => ['description' => 'Use these endpoints to manage the users registered within Firefly III. You need to have the "owner" role to access these endpoints.'],
    //
    //    // user
    //    'destruction'       => ['description' => 'This endpoint allows you to mass-destroy user data.',],
    //    'preferences'       => ['description' => 'These endpoints can be used to manage the user\'s preferences, including some hidden ones.'],
    //    'webhooks'          => ['description' => 'These endpoints can be used to manage the user\'s webhooks and triggers them if necessary.'],
];

