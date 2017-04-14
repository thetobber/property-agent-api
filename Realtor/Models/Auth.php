<?php
namespace Realtor\Models;

use Realtor\Repositories\UsersRepository;
use Realtor\Models\Http\Stream;

class Auth
{
    public static function signIn($email, $password)
    {
        $file = new Stream(fopen(__DIR__.'/../Data/Users.json', 'r'));
        $users = (string) $file;
        $file->close();
        $users = empty($users) ? null : json_decode($users, true);

        if ($users === null || !isset($users[$email])) {
            return false;
        }

        if (
            strtolower($email) === strtolower($users[$email]['email']) && 
            $password === $users[$email]['password']
        ) {
            $_SESSION['verified'] = true;
            $_SESSION['email'] = strtolower($users[$email]['email']);
            $_SESSION['role'] = $users[$email]['role'];

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

    public static function hasRole(...$roles)
    {
        if (self::isVerified()) {
            foreach($roles as $role) {
                if ($role === $_SESSION['role']) {
                    return true;
                }
            }
        }

        return false;
    }
}