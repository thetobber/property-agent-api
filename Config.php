<?php
namespace PropertyAgent;

/**
* Defines a class which holds relevant information for the application 
* to work, such as keys and credentials for connecting to the database.
*/
abstract class Config
{
    /**
    * The database hostname.
    *
    * @var string
    */
    const DB_HOST = 'localhost';

    /**
    * The name of the database which is to be used.
    *
    * @var string
    */
    const DB_NAME = '';

    /**
    * The username for accessing the database.
    *
    * @var string
    */
    const DB_USER = '';

    /**
    * The password to use in conjuction with the username.
    *
    * @var string
    */
    const DB_PASS = '';

    /**
    * The Google Maps API key which is used to fetch maps from Google 
    * based on addresses.
    *
    * @var string
    */
    const GMAPS_KEY = '';

    /**
    * Limit of items per page for paginated results.
    *
    * @var int
    */
    const PAGE_LIMIT = 6;
}
