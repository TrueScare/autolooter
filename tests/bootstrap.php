<?php

use Symfony\Component\Dotenv\Dotenv;

require dirname(__DIR__) . '/vendor/autoload.php';

if (file_exists(dirname(__DIR__) . '/config/bootstrap.php')) {
    require dirname(__DIR__) . '/config/bootstrap.php';
} elseif (method_exists(Dotenv::class, 'bootEnv')) {
    (new Dotenv())->bootEnv(dirname(__DIR__) . '/.env');
}

//region setting up test db
// clear existing db beforehand
passthru('php bin/console doctrine:database:drop -f');
// create fresh db
passthru('php bin/console doctrine:database:create');
// get done with the migration processes
passthru('echo yes | php bin/console doctrine:migrations:migrate');
// load dummy data
passthru('echo yes | php bin/console doctrine:fixtures:load');
//endregion