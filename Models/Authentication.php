<?php
namespace PropertyAgent\Models;

use PDO;
use PDOException;
use PropertyAgent\Data\DbContext;
use Respect\Validation\Validator;

/**
* Defines a class with methods that can be used to verify what the current 
* user can access or sign an user in or out of the application.
*/
abstract class Authentication
{
    /**
    * An array of the different scopes in the application which dictates
    * what an user can access.
    *
    * @var array
    */
    const SCOPES = array(
        'normal' => true,
        'realtor' => true,
        'admin' => true,
        'superadmin' => true
    );

    /**
    * Validates the username and password signing in the user if the credentials 
    * are valid and stores some information about the user in their session.
    *
    * @param string $username
    * @param string $password
    * @return bool Return true if the verification is a success and false on error.
    */
    public static function signIn($username, $password)
    {
        $usernameValidator = Validator::stringType()
                    ->noWhitespace()
                    ->alnum('-_')
                    ->length(4, 200);

        $passwordValidator = Validator::stringType()
                    ->notEmpty()
                    ->length(6, 255);

        if (!$usernameValidator->validate($username) || !$passwordValidator->validate($password)) {
            return false;
        }

        $pdo = DbContext::getContext();

        try {
            $statement = $pdo->prepare('CALL getUserForSignIn(?, ?)');

            $statement
                ->bindParam(1, $username, PDO::PARAM_STR, 200);
            $statement
                ->bindParam(2, $password, PDO::PARAM_STR, 255);

            $statement->execute();
        } catch (PDOException $exception) {
            return false;
        }

        if (($user = $statement->fetch(PDO::FETCH_ASSOC)) !== false) {
            if (in_array(null, $user, true)) {
                return false;
            }

            $_SESSION['username'] = $user['username'];
            $_SESSION['scopes'] = array_flip(explode(',', $user['scopes']));
            $_SESSION['verified'] = true;

            return true;
        }

        return false;
    }

    /**
    * Unsets and destroy the user's session which cause them to be signed out
    * of the application. The session is then started a new.
    */
    public static function signOut()
    {
        session_unset();
        session_destroy();
        session_start();
    }

    /**
    * Checks the session of the user to verify that they're signed in.
    *
    * @return bool
    */
    public static function isVerified()
    {
        return (isset($_SESSION['verified']) && $_SESSION['verified'] === true);
    }

    /**
    * Checks if theuser is signed in and if their username is the same as 
    * the given argument. This is for example used to check if the user has 
    * permission to view their own profile in case they do not have an admin 
    * or super admin scope.
    *
    * @return bool
    */
    public static function isSameUser($username)
    {
        if (self::isVerified()) {
            return ($_SESSION['username'] === $username);
        }

        return false;
    }

    /**
    * Checks if the user is signed in and have one of the one of the scopes 
    * given in the arguments. This will match the first scope that the user 
    * have and return true, if they have 1 of the given scopes or false if 
    * they have none.
    *
    * @return bool
    */
    public static function hasScopes(...$scopes)
    {
        if (self::isVerified()) {
            foreach ($scopes as $scope) {
                if (isset(self::SCOPES[$scope], $_SESSION['scopes'][$scope])) {
                    return true;
                }
            }
        }

        return false;
    }
}
