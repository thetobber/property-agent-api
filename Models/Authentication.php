<?php
namespace PropertyAgent\Models;

use PDO;
use PDOException;
use PropertyAgent\Data\DbContext;
use Respect\Validation\Validator;

abstract class Authentication
{
    const SCOPES = array(
        'normal' => true,
        'realtor' => true,
        'admin' => true,
        'superadmin' => true
    );

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

    public static function signOut()
    {
        session_unset();
        session_destroy();
        session_start();
    }

    public static function isVerified()
    {
        return (isset($_SESSION['verified']) && $_SESSION['verified'] === true);
    }

    public static function isSameUser($username)
    {
        if (self::isVerified()) {
            return ($_SESSION['username'] === $username);
        }

        return false;
    }

    public static function hasScopes(...$scopes)
    {
        if (self::isVerified()) {
            foreach($scopes as $scope) {
                if (isset(self::SCOPES[$scope], $_SESSION['scopes'][$scope])) {
                    return true;
                }
            }

        }

        return false;
    }
}