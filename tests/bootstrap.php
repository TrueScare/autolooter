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
passthru(
    sprintf(
        'php bin/console doctrine:database:drop -f --if-exists --env=%s',
        $_ENV['APP_ENV']
    )
);
// create fresh db
passthru(
    sprintf(
        'php bin/console doctrine:database:create --if-not-exists --env=%s',
        $_ENV['APP_ENV']
    )
);
// get done with the migration processes
passthru(
    sprintf(
        'echo yes | php bin/console doctrine:migrations:migrate --env=%s',
        $_ENV['APP_ENV']
    )
);
// load dummy data
passthru(
    sprintf(
        'echo yes | php bin/console doctrine:fixtures:load --env=%s',
        $_ENV['APP_ENV']
    )
);
//endregion