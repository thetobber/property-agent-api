<?php
namespace PropertyAgent\Repositories;

use PropertyAgent\Repositories\RepositoryTrait;

class UsersRepository extends RepositoryTrait
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
}