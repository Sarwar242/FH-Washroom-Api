<?php
// config/l5-swagger.php

return [
    'defaults' => [
        'proxy' => false,
        'operations_sort' => env('L5_SWAGGER_OPERATIONS_SORT', null),
        'additional_config_url' => null,
        'validator_url' => null,
        'securityDefinitions' => [
            'securitySchemes' => [
            ],
            'security' => [

                [

                ],
            ],
        ],

        'ui' => [
            'display' => [
                'dark_mode' => env('L5_SWAGGER_UI_DARK_MODE', false),

                'doc_expansion' => env('L5_SWAGGER_UI_DOC_EXPANSION', 'none'),

                'filter' => env('L5_SWAGGER_UI_FILTERS', true), // true | false
            ],

            'authorization' => [
                /*
                 * If set to true, it persists authorization data, and it would not be lost on browser close/refresh
                 */
                'persist_authorization' => env('L5_SWAGGER_UI_PERSIST_AUTHORIZATION', false),

                'oauth2' => [
                    /*
                     * If set to true, adds PKCE to AuthorizationCodeGrant flow
                     */
                    'use_pkce_with_authorization_code_grant' => false,
                ],
            ],
        ],
        'routes' => [
            /*
             * Route for accessing api documentation interface
             */
            'api' => 'api/documentation',

            /*
             * Route for accessing parsed swagger annotations.
             */
            'docs' => 'docs',

            /*
             * Route for Oauth2 authentication callback.
             */
            'oauth2_callback' => 'api/oauth2-callback',

            /*
             * Middleware allows to prevent unexpected access to API documentation
             */
            'middleware' => [
                'api',
                // Uncomment below if you want to require authentication
                // 'auth',
            ],

            /*
             * Route Group options
             */
            'group_options' => [],
        ],

        'paths' => [
            /*
             * Absolute path to location where parsed swagger annotations will be stored
             */
            'docs' => storage_path('api-docs'),

            /*
             * File name of the generated json documentation file
             */
            'docs_json' => 'api-docs.json',

            /*
             * File name of the generated YAML documentation file
             */
            'docs_yaml' => 'api-docs.yaml',

            /*
             * Set this to `json` or `yaml` to determine which documentation file to use in UI
             */
            'format_to_use_for_docs' => env('L5_FORMAT_TO_USE_FOR_DOCS', 'json'),

            /*
             * Absolute paths to directory containing the swagger annotations are stored.
             */
            'annotations' => [
                base_path('app'),
            ],
            'base' => env('L5_SWAGGER_BASE_PATH', null),
            'excludes' => [],
        ],
    ],

    'documentations' => [
        'default' => [
            'api' => [
                'title' => 'Toilet Finder API Documentation',
            ],

            'routes' => [
                /*
                 * Route for accessing api documentation interface
                 */
                'api' => 'api/documentation',
            ],
            'paths' => [
                /*
                 * Edit to include full URL in ui for example: 'http://path to your api'
                 */
                'use_absolute_path' => env('L5_SWAGGER_USE_ABSOLUTE_PATH', true),

                /*
                 * File name of the generated json documentation file
                 */
                'docs_json' => 'api-docs.json',

                /*
                 * File name of the generated YAML documentation file
                 */
                'docs_yaml' => 'api-docs.yaml',

                /*
                * Set this to `json` or `yaml` to determine which documentation file to use in UI
                */
                'format_to_use_for_docs' => env('L5_FORMAT_TO_USE_FOR_DOCS', 'json'),

                /*
                 * Absolute paths to directory containing the swagger annotations are stored.
                 */
                'annotations' => [
                    base_path('app'),
                ],
            ],
        ],
    ],

    'security' => [
        /*
         * Examples of Security definitions
         */
        'security_definitions' => [
            'bearer_token' => [
                'type' => 'apiKey',
                'description' => 'Enter token in format (Bearer <token>)',
                'name' => 'Authorization',
                'in' => 'header',
            ],
        ],
    ],
];
