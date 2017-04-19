<?php
namespace PropertyAgent\Controllers;

use PropertyAgent\Models\ControllerTrait;
use PropertyAgent\Models\Authentication as Auth;

class AuthController extends ControllerTrait
{
    public function signIn()
    {
        $body = $this->request->getParsedBody();

        if (!isset($body['username'], $body['password'])) {
            return $this->status(400);
        }

        if (Auth::signIn($body['username'], $body['password'])) {
            return $this->status(204);
        }

        return $this->status(400);
    }

    public function signOut()
    {
        Auth::signOut();
        return $this->status(204);
    }
}