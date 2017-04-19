<?php
namespace PropertyAgent\Controllers;

use PropertyAgent\Models\ControllerTrait;
use PropertyAgent\Repositories\UsersRepository;
use Respect\Validation\Validator;
use PropertyAgent\Models\Authentication as Auth;

/**
* Defines a controller which handles the incoming request concerning users.
*/
class UsersController extends ControllerTrait
{
    /**
    * Returns a single user from the database based on a username. An user can 
    * only be returned if the requestee has an admin or superadmin scope. The 
    * only exception to this is that users can view their own profile.
    *
    * @return Response Returns a response object with a status code and json 
    *   if the request was a success.
    */
    public function getUser()
    {
        if (!Auth::isSameUser($this->params['username']) && !Auth::hasScopes('admin', 'superadmin')) {
            return $this->status(403);
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
    * Returns all users if th requestee has either the admin or super admin 
    * scope. The result is paginated based on the request path.
    *
    * @return Response Returns either a status code or json on success.
    */
    public function getUsers()
    {
        if (!Auth::hasScopes('admin', 'superadmin')) {
            return $this->status(403);
        }

        $limit = 2;
        $page = isset($this->params['page']) ? (int) $this->params['page'] : 0;

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
    * Creates a single user in the database. The submitted user information is 
    * validated and a status code is returned based on the success or failure 
    * of the request. This does not require any scopes as it's used for signing
    * up new users. All users get a default scope of normal.
    *
    * @return Response Returns a status code.
    */
    public function createUser()
    {
        $body = $this->request->getParsedBody();

        $userValidator = Validator::arrayType()
            ->key(
                'username',
                Validator::stringType()
                    ->noWhitespace()
                    ->alnum('-_')
                    ->length(4, 200)
            )
            ->key(
                'email',
                Validator::email()
            )
            ->key(
                'password',
                Validator::stringType()
                    ->notEmpty()
                    ->length(6, 255)
            );

        if (!$userValidator->validate($body)) {
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
    * Updates the information about a single user. The requestee is required to 
    * be the same user or have a scope of admin or superadmin. The submitted 
    * information is validated. A status code is returned based on the success 
    * of the request.
    *
    * @return Response Returns a status code.
    */
    public function updateUser()
    {
        if (!Auth::isSameUser($this->params['username']) || !Auth::hasScopes('admin', 'superadmin')) {
            return $this->status(403);
        }

        $body = $this->request->getParsedBody();

        $userValidator = Validator::arrayType()
            ->key(
                'email',
                Validator::email()
            )
            ->key(
                'password',
                Validator::stringType()
                    ->notEmpty()
                    ->length(6, 255)
            );

        if (!$userValidator->validate($body)) {
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