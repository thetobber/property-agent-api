<?php
namespace PropertyAgent\Controllers;

use PropertyAgent\Models\ControllerTrait;
use PropertyAgent\Models\Authentication as Auth;

class AuthController extends ControllerTrait
{
    public function verified()
    {
        if (Auth::isVerified()) {
            return $this->status(204);
        }

        return $this->status(403);
    }

    public function scopes()
    {
        $body = $this->request->getParsedBody();

        if (empty($body)) {
            return $this->status(400);
        }

        foreach ($body as $scope => $value) {
            if (Auth::hasScopes($scope)) {
                return $this->status(204);
            }
        }

        return $this->status(403);
    }

    public function signIn()
    {
        $body = $this->request->getParsedBody();

        if (!isset($body['username'], $body['password'])) {
            return $this->status(400);
        }

        if (Auth::signIn($body['username'], $body['password'])) {
            return $this->json(array(
                'username' => $_SESSION['username'],
                'scopes' => $_SESSION['scopes']
            ), 200);
        }

        return $this->status(400);
    }

    public function signOut()
    {
        Auth::signOut();
        return $this->status(204);
    }
}