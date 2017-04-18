<?php
namespace PropertyAgent\Repositories;

use PDO;
use PDOException;
use PropertyAgent\Data\DbContext;

class UsersRepository
{
    const ROLES = array(
        1 => 'normal',
        2 => 'realtor',
        3 => 'admin',
        4 => 'superadmin'
    );

    /*public function __construct()
    {
        parent::__construct(
            __DIR__.'/../Data/Users.json',
            array(
                'name' => '@^.+?$@',
                'email' => '@^.+\@.+?$@',
                'password' => '@^.+?$@',
                'role' => null
            )
        );
    }*/

    /**
    * Get a single user from the database by their username.
    *
    * @param string $username
    * @return int|array Returns int on error or array with the user on success.
    */
    public function get($username)
    {
        $pdo = DbContext::getContext();

        try {
            $statement = $pdo
                ->prepare('SELECT `id`, `email`, `username` FROM `users` WHERE `username` = ?');

            $statement
                ->bindParam(1, $username, PDO::PARAM_STR, 255);

            $statement
                ->execute();
        } catch (PDOException $exception) {
            return 500;
        }

        if (($user = $statement->fetch(PDO::FETCH_ASSOC)) !== false) {
            return $user;
        }

        return 404;
    }

    /**
    * Return a limited number of users with an offset. This is used for pagination.
    *
    * @param int $limit
    * @param int $offset
    * @return int|array Returns int on error or array with the users on success.
    */
    public function getAll($limit, $offset)
    {
        $pdo = DbContext::getContext();

        try {
            $statement = $pdo
                ->prepare('SELECT `id`, `email`, `username` FROM `users` LIMIT ? OFFSET ?');

            $statement
                ->bindParam(1, $limit, PDO::PARAM_INT);

            $statement
                ->bindParam(2, $offset, PDO::PARAM_INT);

            $statement
                ->execute();
        } catch (PDOException $exception) {
            return 500;
        }

        $users = $statement->fetchAll(PDO::FETCH_ASSOC);

        if ($users !== false && !empty($users)) {
            return $users;
        }

        return 404;
    }

    /**
    * Return the total count of all the user records.
    *
    * @return int
    */
    public function getCount()
    {
        $pdo = DbContext::getContext();

        try {
            $statement = $pdo
                ->prepare('SELECT COUNT(*) AS `count` FROM `users`');

            $statement
                ->execute();
        } catch (PDOException $exception) {
            return 500;
        }

        $count = $statement->fetch(PDO::FETCH_ASSOC);

        if ($count !== false && !empty($count)) {
            return $count['count'];
        }

        return 0;
    }

    /**
    * Inserts an user into the database.
    *
    * @param array $model The required user information.
    * @return int Returns an integer corresponding to a HTTP status code.
    */
    public function create(array $model)
    {
        $pdo = DbContext::getContext();

        try {
            $statement = $pdo
                ->prepare('CALL createUser(?, ?, ?)');

            $statement
                ->bindParam(1, $model['username'], PDO::PARAM_STR, 255);
            $statement
                ->bindParam(2, $model['email'], PDO::PARAM_STR, 255);
            $statement
                ->bindParam(3, $model['password'], PDO::PARAM_STR, 255);

            $statement
                ->execute();
        } catch (PDOException $exception) {
            $errorCode = $exception
                ->getCode();

            //On duplicate key
            if ($errorCode === '23000') {
                return 409;
            }

            return 500;
        }

        return 201;
    }

    /**
    * Updates an user in the database.
    *
    * @param array $model The required user information.
    * @return int Returns an integer corresponding to a HTTP status code.
    */
    public function update($username, array $model)
    {
        $pdo = DbContext::getContext();

        try {
            $statement = $pdo
                ->prepare('UPDATE `users` SET `email` = ?, `password` = ? WHERE `username` = ?');

            $statement
                ->bindParam(1, $model['email'], PDO::PARAM_STR, 255);
            $statement
                ->bindParam(2, $model['password'], PDO::PARAM_STR, 255);
            $statement
                ->bindParam(3, $username, PDO::PARAM_STR, 255);

            $statement
                ->execute();
        } catch (PDOException $exception) {
            return 500;
        }

        return 204;
    }
}
