<?php
namespace PropertyAgent\Controllers;

use PDO;
use PDOException;
use PropertyAgent\Models\ControllerTrait;
use PropertyAgent\Models\Http\ServerRequest;
use PropertyAgent\Models\Http\Response;
use PropertyAgent\Data\DbContext;

/**
* @todo
*/
class UsersController extends ControllerTrait
{
    /**
    * @todo
    */
    public function getUser()
    {
        $pdo = DbContext::getContext();

        $statement = $pdo->prepare('CALL getUser(?)');

        $statement->bindParam(1, $this->params['username'], PDO::PARAM_STR, 255);

        if ($statement->execute() && ($user = $statement->fetch(PDO::FETCH_ASSOC)) !== false) {
            return $this->json(
                $user,
                200
            );
        }

        return $this->status(404);
    }

    /**
    * @todo
    */
    public function getUsers()
    {
        return $this->status(501);
    }

    /**
    * @todo
    */
    public function createUser()
    {
        $body = $this->request->getParsedBody();
        $pdo = DbContext::getContext();
        $statement = $pdo->prepare('CALL createUser(?, ?, ?)');

        try {
            $statement->bindParam(1, $body['username'], PDO::PARAM_STR, 255);
            $statement->bindParam(2, $body['email'], PDO::PARAM_STR, 255);
            $statement->bindParam(3, $body['password'], PDO::PARAM_STR, 255);

            $statement->execute();

        } catch (PDOException $exception) {
            $errorCode = $exception->getCode();

            if ($errorCode === '23000') {
                return $this->status(409);
            }

            return $this->text($errorCode, 500);
        }


        return $this->status(201);
    }

    /**
    * @todo
    */
    public function updateUser()
    {
        return $this->status(501);
    }

    /**
    * @todo
    */
    public function deleteUser()
    {
        return $this->status(501);
    }
}