<?php
namespace Realtor\Controllers;

use Realtor\Models\ControllerTrait;
use Realtor\Models\Http\ServerRequest;
use Realtor\Models\Http\Response;
use Realtor\Models\Http\Stream;
use Realtor\Models\Auth;
use Realtor\Repositories\UsersRepository;

/**
* @todo
*/
class UsersController extends ControllerTrait
{
    protected $repository;

    public function __construct(ServerRequest $request, Response $response)
    {
        parent::__construct($request, $response);
        $this->repository = new UsersRepository();
    }

    /**
    * @todo
    */
    public function getUser()
    {
        if (!Auth::hasRole('admin') || !Auth::hasRole('superadmin')) {
            return $this->text('', 403);
        }

        $params = $this->request->getAttribute('routeParams');
        $user = $this->repository->get($params['id']);

        if ($user === null) {
            return $this->text('', 404);
        }

        return $this->json($user);
    }

    /**
    * @todo
    */
    public function getUsers()
    {
        if (!Auth::hasRole('admin', 'superadmin')) {
            return $this->text('', 403);
        }

        return $this->json(
            $this->repository->getAll()
        );
    }

    /**
    * @todo
    */
    public function createUser()
    {
        $params = $this->request->getAttribute('routeParams');
        $body = $this->request->getParsedBody();

        if (empty($body)) {
            return $this->text('', 400);
        }

        $body['role'] = UsersRepository::ROLES[0];
        $isCreated = $this->repository->create($body, 'email');

        if ($isCreated === null) {
            return $this->text('Username taken', 400);
        } else if ($isCreated) {
            return $this->text('', 201);
        }

        return $this->text('', 400);
    }

    /**
    * @todo
    */
    public function updateUser()
    {
        if (!Auth::hasRole('admin', 'superadmin')) {
            return $this->text('', 403);
        }

        $params = $this->request->getAttribute('routeParams');
        $body = $this->request->getParsedBody();

        if (empty($body)) {
            return $this->text('', 400);
        }

        if ($this->repository->update($params['id'], $body)) {
            return $this->text('', 204);
        }

        return $this->text('', 404);
    }

    /**
    * @todo
    */
    public function deleteUser()
    {
        if (!Auth::hasRole('admin', 'superadmin')) {
            return $this->text('', 403);
        }

        $params = $this->request->getAttribute('routeParams');

        if ($this->repository->delete($params['id'])) {

            if ($params['id'] === $_SESSION['email']) {
                Auth::signOut();
            }

            return $this->text('', 204);
        }

        return $this->text('', 404);
    }

    public function signIn()
    {
        $body = $this->request->getParsedBody();

        if (empty($body['email']) || empty($body['password'])) {
            return $this->json(array(
                'error' => 'Failed to sign in'
            ), 400);
        }

        if (Auth::signIn($body['email'], $body['password'])) {
            return $this->json(array(
                'user' => $_SESSION['email'],
                'role' => $_SESSION['role']
            ), 200);
        }

        return $this->json(array(
            'error' => 'Failed to sign in'
        ), 400);
    }

    public function signOut()
    {
        Auth::signOut();
        return $this->text('', 204);
    }
}