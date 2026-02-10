<?php
namespace Deployer;

require 'recipe/laravel.php';

set('application', 'officeserver');
set('repository', 'git@github.com:YOUR_ORG/officeserver.git');

set('keep_releases', 5);
set('release_name', fn () => date('YmdHis'));

add('shared_files', ['.env']);
add('shared_dirs', ['storage']);
add('writable_dirs', ['storage', 'bootstrap/cache']);

after('deploy:failed', 'deploy:unlock');

host('staging')
    ->setHostname(getenv('SERVER_HOST'))
    ->setRemoteUser(getenv('SERVER_USER'))
    ->setDeployPath('/var/www/officeserver');
