<?php
session_start();
ini_set('html_errors', false);
require(__DIR__.'/vendor/autoload.php');

use PropertyAgent\Models\Application;
use PropertyAgent\Models\Utilities;
use PropertyAgent\Models\Authentication as Auth;

$app = new Application();

// Is user signed in?
$app->registerRoute(
    'GET',
    '@^/auth/verified/$@i',
    'AuthController',
    'verified'
);

// Sign user in
$app->registerRoute(
    'POST',
    '@^/auth/signin/$@i',
    'AuthController',
    'signIn'
);

// Sign user out
$app->registerRoute(
    'POST',
    '@^/auth/signout/$@i',
    'AuthController',
    'signOut'
);





// Registering AuthController for use in routes
$app->registerController(
    'AuthController',
    'PropertyAgent\Controllers\AuthController'
);

// Registering PropertiesController for use in routes
$app->registerController(
    'PropertiesController',
    'PropertyAgent\Controllers\PropertiesController'
);

// Registering UsersController for use in routes
$app->registerController(
    'UsersController',
    'PropertyAgent\Controllers\UsersController'
);





// Get all properties paginated
$app->registerRoute(
    'GET',
    '@^/(properties|properties/p/(?<page>[0-9]+?))/$@i',
    'PropertiesController',
    'getProperties'
);

// Get signle property by id
$app->registerRoute(
    'GET',
    '@^/properties/(?<id>[0-9]+?)/$@i',
    'PropertiesController',
    'getProperty'
);

// Create a property
$app->registerRoute(
    'POST',
    '@^/properties/$@i',
    'PropertiesController',
    'createProperty'
);

// Update a property by id
$app->registerRoute(
    'POST',
    '@^/properties/(?<id>[0-9]+?)/$@i',
    'PropertiesController',
    'updateProperty'
);

// Delete a property by id
$app->registerRoute(
    'POST',
    '@^/properties/d/(?<id>[0-9]+?)/$@i',
    'PropertiesController',
    'deleteProperty'
);






// Get all users paginated
$app->registerRoute(
    'GET',
    '@^/(users|users/p/(?<page>[0-9]+?))/$@i',
    'UsersController',
    'getUsers'
);

// Fetch a single user by username
$app->registerRoute(
    'GET',
    '@^/users/(?<username>[a-z0-9_-]+?)/$@i',
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
    '@^/users/(?<username>[a-z0-9_-]+?)/$@i',
    'UsersController',
    'updateUser'
);

$app->run();