<?php
namespace PropertyAgent\Controllers;

use PDO;
use PDOException;
use PropertyAgent\Models\ControllerTrait;
use PropertyAgent\Models\Http\ServerRequest;
use PropertyAgent\Models\Http\Response;
use PropertyAgent\Data\DbContext;
use PropertyAgent\Repositories\UsersRepository;

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
        if (empty($this->params['username'])) {
            return $this->status(404);
        }

        $repo = new UsersRepository();

        $user = $repo->get($this->params['username']);

        if ($user === 404) {
            return $this->status(404);
        }
        
        if ($user === 500) {
            return $this->status(500);
        }

        return $this->json($user, 200);
    }

    /**
    * @todo
    */
    public function getUsers()
    {
        $limit = 2;
        $page = (int) $this->params['page'] ?? 0;

        if ($page >= 1) {
            $page--;
        } else {
            $page = 0;
        }

        $repo = new UsersRepository();

        $users = $repo->getAll($limit, $page * $limit);

        if ($users === 404) {
            return $this->status(404);
        }
        
        if ($users === 500) {
            return $this->status(500);
        }

        $total = $repo->getCount();
        $maxPages = 0;

        if ($total !== 0 && $limit !== 0) {
            $maxPages = $total > $limit ? ceil($total/$limit) : ceil($limit/$total);
        }

        $users = array(
            'maxPages' => $maxPages,
            'users' => $users
        );

        return $this->json($users, 200);
    }

    /**
    * @todo
    */
    public function createUser()
    {
        $body = $this->request->getParsedBody();

        if (empty($body['username']) || empty($body['email']) || empty($body['password'])) {
            return $this->status(400);
        }

        $repo = new UsersRepository();

        $status = $repo->create($body);

        if ($status === 201) {
            return $this->status(201);
        }

        if ($status === 409) {
            return $this->status(409);
        }
        
        if ($status === 500) {
            return $this->status(500);
        }
    }

    /**
    * @todo
    */
    public function updateUser()
    {
        $body = $this->request->getParsedBody();

        if (empty($body['email']) || empty($body['password'])) {
            return $this->status(400);
        }

        $repo = new UsersRepository();

        $status = $repo->update($this->params['username'], $body);

        if ($status === 204) {
            return $this->status(204);
        }

        if ($status === 500) {
            return $this->status(500);
        }
    }
}