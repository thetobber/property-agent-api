<?php
//session_start();
require(__DIR__.'/vendor/autoload.php');

use PropertyAgent\Models\Application;
use PropertyAgent\Models\Utilities;

$app = new Application();

$app->registerController(
    'UsersController',
    'PropertyAgent\Controllers\UsersController'
);

$app->registerRoute(
    'GET',
    '@^/users/(?<username>[a-z]+?)/$@i',
    'UsersController',
    'getUser'
);

$app->registerRoute(
    'POST',
    '@^/users/$@i',
    'UsersController',
    'createUser'
);

//Utilities::print($app->routes);
//Utilities::print($app->controllers);

$app->run();
//Utilities::print($app->request->getUri());

/*$app->registerController(
    'UsersController',
    'Realtor\Controllers\UsersController'
);

$app->registerController(
    'PropertiesController',
    'Realtor\Controllers\PropertiesController'
);

$app->registerRoute(
    'POST',
    '@^/app/signin/$@i',
    'UsersController',
    'signIn'
);

$app->registerRoute(
    'POST',
    '@^/app/signout/$@i',
    'UsersController',
    'signOut'
);

$app->registerRoute(
    'GET',
    '@^/app/users/$@i',
    'UsersController',
    'getUsers'
);

$app->registerRoute(
    'GET',
    '@^/app/users/(?<id>.+?\@.+?\..+?)/$@i',
    'UsersController',
    'getUser'
);

$app->registerRoute(
    'POST',
    '@^/app/users/$@i',
    'UsersController',
    'createUser'
);

$app->registerRoute(
    'POST',
    '@^/app/users/update/(?<id>.+?\@.+?\..+?)/$@i',
    'UsersController',
    'updateUser'
);
$app->registerRoute(
    'POST',
    '@^/app/users/delete/(?<id>.+?\@.+?\..+?)/$@i',
    'UsersController',
    'deleteUser'
);

$app->registerRoute(
    'GET',
    '@^/app/properties/(?<id>[a-f0-9]{32})/$@i',
    'PropertiesController',
    'getProperty'
);
$app->registerRoute(
    'GET',
    '@^/app/properties/$@i',
    'PropertiesController',
    'getProperties'
);
$app->registerRoute(
    'POST',
    '@^/app/properties/$@i',
    'PropertiesController',
    'createProperty'
);
$app->registerRoute(
    'POST',
    '@^/app/properties/update/(?<id>[a-f0-9]{32})/$@i',
    'PropertiesController',
    'updateProperty'
);
$app->registerRoute(
    'POST',
    '@^/app/properties/delete/(?<id>[a-f0-9]{32})/$@i',
    'PropertiesController',
    'deleteProperty'
);

$app->run();*/