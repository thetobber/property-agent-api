<?php
//session_start();
require(__DIR__.'/vendor/autoload.php');

use PropertyAgent\Models\Application;
use PropertyAgent\Models\Utilities;

$app = new Application();

// Registering users controller for use in routes
$app->registerController(
    'UsersController',
    'PropertyAgent\Controllers\UsersController'
);

// Get all users paginated
$app->registerRoute(
    'GET',
    '@^/(users|users/(?<page>[0-9]+?))/$@i',
    'UsersController',
    'getUsers'
);

// Fetch a single user by username
$app->registerRoute(
    'GET',
    '@^/users/(?<username>[a-z0-9]+?)/$@i',
    'UsersController',
    'getUser'
);

// Create a new user
$app->registerRoute(
    'POST',
    '@^/users/$@i',
    'UsersController',
    'createUser'
);

// Update an existing user by username
$app->registerRoute(
    'POST',
    '@^/users/(?<username>[a-z0-9]+?)/$@i',
    'UsersController',
    'updateUser'
);

$app->run();

//Utilities::print($app->routes);
//Utilities::print($app->controllers);