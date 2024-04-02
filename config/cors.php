<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Cross-Origin Resource Sharing (CORS) Configuration
    |--------------------------------------------------------------------------
    |
    | Here you may configure your settings for cross-origin resource sharing
    | or "CORS". This determines what cross-origin operations may execute
    | in web browsers. You are free to adjust these settings as needed.
    |
    | To learn more: https://developer.mozilla.org/en-US/docs/Web/HTTP/CORS
    |
    */

    'paths' => [
        'api/*',
        'sanctum/csrf-cookie',
        'login',
        'user/info',
        'clients',
        'clients/*',
        'contractors',
        'banks',
        'introducer',
        'accounts',
        'expenses',
        'contractors/*',
        'banks/*',
        'introducer/*',
        'accounts/*',
        'expenses/*',
        'client_details',
        'charge_history',
        'task_management',
        'client_details/*',
        'charge_history/*',
        'task_management/*',
        'log_task',
        'transactions',
        'income',
        'income-expenditure',
        'invoices',
        'invoices/*',
        'account-fee',
        'get_contractors',
        'get_clients',
        'list_id_account',
        'check_unique_email',
        'get-client-aggregation-today',
        'list_contractor_id',
        'check_unique_email_introducer',
        'check_unique_email_client',
        'get_client_with_contractor',
        'get_introducer_with_contractor',
        'get_list_bank_name',
        'account_balance_history',
        'get_account_balance_history',
        'get_account_number_by_client',
        'invoice_contructor',
        'invoice_contructor/*',
        'list_contractors',
        'check_invoice_contractor_number/*',
        'export_pdf_account',
        'export-pdf',
        'log_task',
        'log_task/*',
        'check_unique_client_id',
        'check_unique_account_number',
        'check_contract_detail',
        'download',
        'update_task_management',
        'income_expenditure/*',
        'export_income',
    ],

    'allowed_methods' => ['*'],

    'allowed_origins' => ['*'],

    'allowed_origins_patterns' => [],

    'allowed_headers' => ['*'],

    'exposed_headers' => [],

    'max_age' => 0,

    'supports_credentials' => true,

];
