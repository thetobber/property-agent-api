<?php
namespace PropertyAgent\Repositories;

use PropertyAgent\Repositories\RepositoryTrait;

class UsersRepository extends RepositoryTrait
{
    const ROLES = array(
        'normal',
        'admin',
        'superadmin'
    );

    public function __construct()
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
    }
}