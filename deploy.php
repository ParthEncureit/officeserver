<?php
namespace Deployer;

require 'recipe/laravel.php';

set('application', 'officeserver');
set('keep_releases', 5);

add('shared_files', ['.env']);
add('shared_dirs', ['storage']);
add('writable_dirs', ['storage', 'bootstrap/cache']);

set('php_fpm_version', '8.4');

after('deploy:failed', 'deploy:unlock');

host('staging')
    ->setHostname(getenv('SERVER_HOST'))
    ->setRemoteUser(getenv('SERVER_USER'))
    ->setDeployPath('/var/www/officeserver');

host('production')
    ->setHostname(getenv('SERVER_HOST'))
    ->setRemoteUser(getenv('SERVER_USER'))
    ->setDeployPath('/var/www/officeserver');
