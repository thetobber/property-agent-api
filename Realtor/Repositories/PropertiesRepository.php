<?php
namespace Realtor\Repositories;

use Realtor\Repositories\RepositoryTrait;

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