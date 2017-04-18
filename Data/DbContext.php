<?php
namespace PropertyAgent\Data;

use PDO;
use PDOException;
use PropertyAgent\Data\DbConfig;

/**
* Defines a context which can be used to create/get a singleton instance
* of a PDO object to communicate with a database.
*/
class DbContext
{
    /**
    * The single instance of a PDO object.$_COOKIE
    *
    * @var PDO
    */
    private static $pdo;

    /**
    * Constructor has been set to private so this class cannot be instantiated
    * outside its own scope.
    */
    private function __construct()
    {
    }

    /**
    * Creates a new instance of PDO with the settings from DbConfig or returns 
    * the same instance if this method has been called before.
    *
    * @return PDO
    */
    public static function getContext()
    {
        if (self::$pdo === null) {
            try {
                self::$pdo = new PDO(
                    DbConfig::DRIVER.':host='.DbConfig::HOST.';dbname='.DbConfig::DBNAME,
                    DbConfig::USER,
                    DbConfig::PASSWORD,
                    array(
                        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                        PDO::ATTR_EMULATE_PREPARES => false
                    )
                );
            } catch (PDOException $exceoption) {
                header(
                    'HTTP/1.1 500 Internal Server Error',
                    true,
                    500
                );

                echo 'abc';

                die();
            }
        }

        return self::$pdo;
    }
}
