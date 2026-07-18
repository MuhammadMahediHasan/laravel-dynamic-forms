<?php

return [
    'table_prefix' => '',
    'route_prefix' => 'api/v1',
    'middleware' => ['api'],

    // Supported locales for bilingual text fields and options
    'locales' => ['en'],

    // Supported dynamic form types
    'form_types' => ['Survey', 'Quiz', 'Assessment', 'Monitoring'],

    // Allowed morph model types for form responses (respondents and subjects)
    'allowed_respondent_types' => [
        'App\Models\User',
    ],
    'allowed_subject_types' => [
        'App\Models\User',
    ],

    // Components that do not store any response value
    'value_skipped_components' => ['Group', 'Header'],

    // Field types that require option fields (e.g. choice lists)
    'option_field_types' => ['select', 'radio', 'checkbox', 'multiSelect'],

    // Automatically load routes and migrations
    'register_routes' => true,
    'load_migrations' => true,
];
