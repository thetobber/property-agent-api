<?php
namespace PropertyAgent\Controllers;

use PropertyAgent\Config;
use PropertyAgent\Models\ControllerTrait;
use PropertyAgent\Repositories\PropertiesRepository;
use Respect\Validation\Validator;
use PropertyAgent\Models\Authentication as Auth;

/**
* @todo
*/
class PropertiesController extends ControllerTrait
{
    const MAPS_KEY = 'AIzaSyAJ4z06vdTUt-T4HAHk-fdsEZ1_Gc1SCmY';

    /**
    * @todo
    */
    public function getProperty()
    {
        $id = (int) $this->params['id'];

        $repo = new PropertiesRepository();

        $property = $repo->get($this->params['id']);

        if ($property === 404) {
            return $this->status(404);
        }
        
        if ($property === 500) {
            return $this->status(500);
        }

        if (!empty($property['images'])) {
            $property['images'] = unserialize($property['images']);
        }

        return $this->json($property, 200);
    }

    /**
    * @todo
    */
    public function getProperties()
    {
        $limit = 2;
        $page = isset($this->params['page']) ? (int) $this->params['page'] : 0;

        if ($page >= 1) {
            $page--;
        } else {
            $page = 0;
        }

        $repo = new PropertiesRepository();

        $total = $repo->getCount();
        $maxPages = 0;

        if ($total !== 0 && $limit !== 0) {
            $maxPages = $total > $limit ? ceil($total/$limit) : ceil($limit/$total);
        }

        if ($page + 1 > $maxPages) {
            return $this->status(404);
        }

        $properties = $repo->getAll($limit, $page * $limit);

        if ($properties === 404) {
            return $this->status(404);
        }
        
        if ($properties === 500) {
            return $this->status(500);
        }

        foreach ($properties as &$property) {
            if (!empty($property['images'])) {
                $property['images'] = unserialize($property['images']);
            }
        }

        $properties = array(
            'maxPages' => $maxPages,
            'properties' => $properties
        );

        return $this->json($properties, 200);
    }

    /**
    * @todo
    */
    public function createProperty()
    {
        if (!Auth::hasScopes('realtor' ,'admin', 'superadmin')) {
            return $this->status(403);
        }

        $body = $this->request->getParsedBody();

        $propertyValidator = Validator::arrayType()
            ->key(
                'type',
                Validator::stringType()->notEmpty()->length(1, 100, true)
            )
            ->key(
                'road',
                Validator::stringType()->notEmpty()->length(1, 200, true)
            )
            ->key(
                'postal',
                Validator::stringType()->notEmpty()->length(4, 4, true)
            )
            ->key(
                'municipality',
                Validator::stringType()->notEmpty()->length(1, 100, true)
            )
            ->key(
                'number',
                Validator::intVal()->between(0, 65535, true)
            )
            ->key(
                'floor',
                Validator::intVal()->between(0, 255, true)
            )
            ->key(
                'door',
                Validator::stringType()->notEmpty()->length(1, 20, true)
            )
            ->key(
                'rooms',
                Validator::intVal()->between(0, 65535, true)
            )
            ->key(
                'area',
                Validator::intVal()->between(0, 4294967295, true)
            )
            ->key(
                'year',
                Validator::stringType()->notEmpty()->length(1, 10, true)
            )
            ->key(
                'expenses',
                Validator::intVal()->between(0, 4294967295, true)
            )
            ->key(
                'deposit',
                Validator::intVal()->between(0, 4294967295, true)
            )
            ->key(
                'price',
                Validator::intVal()->between(0, 18446744073709551615, true)
            );

        if (!$propertyValidator->validate($body)) {
            return $this->status(400);
        }

        //Create a place query with the Google Maps embed API
        $mapQuery = "$body[road] $body[number], $body[floor] $body[door], $body[postal] $body[municipality]";
        $body['map'] = 'https://www.google.com/maps/embed/v1/place?key='.Config::GMAPS_KEY.'&q='.urlencode($mapQuery);

        $repo = new PropertiesRepository();

        $files = $this->request->getUploadedFiles();
        $fileSources = array();
        $filePaths = array();

        if (!empty($files)) {
            $uri = $this->request->getUri();
            $absoluteUri = $uri->getScheme().'://'.$uri->getAuthority(false);

            foreach ($files as $file) {
                $mimeType = $file->getClientMediaType();
                $fileType = '';

                if ($mimeType == 'image/jpeg') {
                    $fileType = '.jpg';
                } else if ($mimeType == 'image/png') {
                    $fileType = '.png';
                } else if ($mimeType == 'image/gif') {
                    $fileType = '.gif';
                } else {
                    return $this->status(415);
                }

                $fileName = md5(uniqid(rand(), true)).$fileType;
                $filePaths[] = "$absoluteUri/Files/$fileName";

                $fileSources[] = array(
                    'path' => __DIR__.'/../Files/'.$fileName,
                    'file' => $file
                );
            }
        }

        $body['images'] = serialize($filePaths);

        $status = $repo->create($body);

        if ($status === 201) {
            foreach ($fileSources as $file) {
                try {
                    $file['file']->moveTo($file['path']);
                } catch (Exception $exception) {
                    //Failure on moving to folder
                }                
            }

            return $this->status(201);
        }

        if ($status === 409) {
            return $this->status(409);
        }
        
        if ($status === 500) {
            return $this->status(500);
        }
    }

    public function updateProperty()
    {
        return $this->status(501);
    }

    public function deleteProperty()
    {
        return $this->status(501);
    }
}