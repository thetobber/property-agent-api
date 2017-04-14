<?php
namespace PropertyAgent\Repositories;

use PropertyAgent\Repositories\RepositoryTrait;

class PropertiesRepository extends RepositoryTrait
{
    public function __construct()
    {
        parent::__construct(
            __DIR__.'/../Data/Properties.json',
            array(
                'roadname' => '@^.+?$@',
                'roadnumber' => '@^\d+?$@',
                'door' => '@^\d+?$@' ,
                'municipality'=> '@^.+?$@',
                'postalcode'=> '@^\d+?$@',
                'images'=> null,
                'map' => null
            )
        );
    }
}