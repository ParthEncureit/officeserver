<?php
namespace Deployer;

require 'recipe/laravel.php';

/*
|--------------------------------------------------------------------------
| Basic project config
|--------------------------------------------------------------------------
*/

set('application', 'officeserver');
set('keep_releases', 5);

/*
|--------------------------------------------------------------------------
| Use timestamped releases (fixes "Release name already exists")
|--------------------------------------------------------------------------
*/
set('release_name', function () {
    return date('YmdHis');
});

/*
|--------------------------------------------------------------------------
| Shared files & directories
|--------------------------------------------------------------------------
| These survive between deployments
|--------------------------------------------------------------------------
*/
add('shared_files', ['.env']);
add('shared_dirs', ['storage']);

/*
|--------------------------------------------------------------------------
| Writable directories
|--------------------------------------------------------------------------
*/
add('writable_dirs', [
    'storage',
    'bootstrap/cache',
]);

/*
|--------------------------------------------------------------------------
| PHP version on server
|--------------------------------------------------------------------------
*/
set('php_fpm_version', '8.4');

/*
|--------------------------------------------------------------------------
| Hooks
|--------------------------------------------------------------------------
*/
after('deploy:failed', 'deploy:unlock');

/*
|--------------------------------------------------------------------------
| Hosts
|--------------------------------------------------------------------------
| Deployer uses SSH automatically
|--------------------------------------------------------------------------
*/

host('staging')
    ->setHostname(getenv('SERVER_HOST'))
    ->setRemoteUser(getenv('SERVER_USER'))
    ->setDeployPath('/var/www/officeserver');

host('production')
    ->setHostname(getenv('SERVER_HOST'))
    ->setRemoteUser(getenv('SERVER_USER'))
    ->setDeployPath('/var/www/officeserver');
