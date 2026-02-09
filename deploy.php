<?php

namespace Deployer;

require 'recipe/laravel.php';

// -------------------------------------------------
// Configuration
// -------------------------------------------------
set('application', 'officeserver');
set('keep_releases', 5);

// CRITICAL: Disable git completely
set('repository', null);

// -------------------------------------------------
// Shared resources (persist across deployments)
// -------------------------------------------------
add('shared_files', [
    '.env',
]);

add('shared_dirs', [
    'storage',
]);

add('writable_dirs', [
    'bootstrap/cache',
    'storage',
    'storage/app',
    'storage/app/public',
    'storage/framework',
    'storage/framework/cache',
    'storage/framework/sessions',
    'storage/framework/views',
    'storage/logs',
]);

// -------------------------------------------------
// Rsync Configuration
// -------------------------------------------------
set('rsync', [
    'exclude' => [
        '.git',
        '.github',
        'node_modules',
        'tests',
        '.env',
        '.env.example',
        'storage/',
        'vendor/',
    ],
    'exclude-file' => false,
    'include' => [],
    'include-file' => false,
    'filter' => [],
    'filter-file' => false,
    'filter-perdir' => false,
    'flags' => 'az',
    'options' => ['delete'],
    'timeout' => 300,
]);

// -------------------------------------------------
// STAGING Environment
// -------------------------------------------------
host('staging')
    ->setHostname(getenv('SERVER_HOST'))
    ->setRemoteUser(getenv('SERVER_USER'))
    ->setDeployPath('/var/www/officeserver-staging')
    ->set('branch', 'staging')
    ->set('http_user', 'www-data')
    ->set('writable_mode', 'chown');

// -------------------------------------------------
// PRODUCTION Environment (main branch)
// -------------------------------------------------
host('production')
    ->setHostname(getenv('SERVER_HOST'))
    ->setRemoteUser(getenv('SERVER_USER'))
    ->setDeployPath('/var/www/officeserver-production')
    ->set('branch', 'main')  // Changed from 'production' to 'main'
    ->set('http_user', 'www-data')
    ->set('writable_mode', 'chown');

// -------------------------------------------------
// Custom Tasks
// -------------------------------------------------

// Install Composer dependencies on server
task('deploy:composer', function () {
    run('cd {{release_path}} && composer install --no-dev --no-interaction --prefer-dist --optimize-autoloader');
});

// Run database migrations
task('deploy:migrate', function () {
    run('cd {{release_path}} && php artisan migrate --force');
});

// Cache Laravel configs for performance
task('deploy:cache', function () {
    run('cd {{release_path}} && php artisan config:cache');
    run('cd {{release_path}} && php artisan route:cache');
    run('cd {{release_path}} && php artisan view:cache');
});

// Create storage link
task('deploy:storage_link', function () {
    run('cd {{release_path}} && php artisan storage:link');
});

// Restart PHP-FPM (optional, for OPcache refresh)
task('deploy:restart_php', function () {
    run('sudo systemctl reload php8.4-fpm', ['timeout' => 30]);
})->onRoles('web');

// Health check
task('deploy:health_check', function () {
    $response = run('curl -f -s -o /dev/null -w "%{http_code}" {{deploy_path}}/current/public/api/health || echo "000"');
    if ($response !== '200') {
        warning("Health check returned: $response");
    } else {
        info('✓ Health check passed');
    }
});

// -------------------------------------------------
// Deployment Flow
// -------------------------------------------------
task('deploy', [
    'deploy:prepare',
    'rsync',                    // Push code from GitHub Actions
    'deploy:composer',          // Install dependencies on server
    'deploy:shared',            // Link shared files/dirs
    'deploy:writable',          // Set permissions
    'deploy:storage_link',      // Create storage symlink
    'deploy:cache',             // Cache configs
    'deploy:migrate',           // Run migrations
    'deploy:publish',           // Swap symlink (zero downtime)
    'deploy:health_check',      // Verify deployment
    'deploy:cleanup',           // Remove old releases
    'deploy:unlock',
])->desc('Deploy the application');

// On failure, unlock and optionally rollback
after('deploy:failed', 'deploy:unlock');