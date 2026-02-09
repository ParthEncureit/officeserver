<?php

namespace Deployer;

require 'recipe/laravel.php';

// -------------------------------------------------
// Application
// -------------------------------------------------
set('application', 'officeserver');

// Disable git completely (CRITICAL)
set('repository', null);
set('deploy_via', 'rsync');

// Keep last releases
set('keep_releases', 5);

// -------------------------------------------------
// Shared files & directories
// -------------------------------------------------
add('shared_files', ['.env']);
add('shared_dirs', ['storage']);

// Writable directories
add('writable_dirs', ['storage', 'bootstrap/cache']);

// PHP version (server)
set('php_fpm_version', '8.4');

// -------------------------------------------------
// Rsync configuration
// -------------------------------------------------
set('rsync', [
    'exclude' => [
        '.git',
        '.github',
        'node_modules',
        'tests',
        'vendor',        // composer runs on server
    ],
    'flags' => 'az',
]);

// -------------------------------------------------
// Hooks
// -------------------------------------------------
after('deploy:failed', 'deploy:unlock');

// -------------------------------------------------
// Hosts (SSH)
// -------------------------------------------------
host('staging')
    ->setHostname(getenv('SERVER_HOST'))
    ->setRemoteUser(getenv('SERVER_USER'))
    ->setDeployPath('/var/www/officeserver');

host('production')
    ->setHostname(getenv('SERVER_HOST'))
    ->setRemoteUser(getenv('SERVER_USER'))
    ->setDeployPath('/var/www/officeserver');
