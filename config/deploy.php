<?php

/*
|--------------------------------------------------------------------------
| FTP deploy from the developer's machine
|--------------------------------------------------------------------------
|
| `php artisan deploy:ftp` uploads the files changed since the last deploy
| over FTP and calls POST {url}/api/deploy on the server. Filled in the LOCAL
| .env only — the server needs none of it. See docs/project/deploy.md.
|
*/

return [

    'ftp' => [
        'host'     => env('DEPLOY_FTP_HOST'),
        'port'     => (int) env('DEPLOY_FTP_PORT', 21),
        'username' => env('DEPLOY_FTP_USERNAME'),
        'password' => env('DEPLOY_FTP_PASSWORD'),
        // Project folder relative to the FTP login — the one holding `artisan`.
        'root' => env('DEPLOY_FTP_ROOT', '/'),
    ],

    // Site address without a trailing slash, e.g. https://nosi.com.ua
    'url' => env('DEPLOY_URL'),

    // DEPLOY_TOKEN of the SERVER .env (the local DEPLOY_TOKEN stays empty).
    'token' => env('DEPLOY_REMOTE_TOKEN'),

    // Tracked files that never go to the server. Prefixes of the repository path.
    'exclude' => [
        '.github/',
        '.claude/',
        'tests/',
        'docs/',
        'design/',
        '.gitattributes',
        '.editorconfig',
        'phpunit.xml',
        'compose.yaml',
        'CLAUDE.md',
        'README.md',
    ],

    // Where on the server the deployed commit is remembered, relative to the root.
    'revision_file' => 'storage/app/.deploy-revision',

];
