<?php
namespace PropertyAgent\Controllers;

use PropertyAgent\Models\ControllerTrait;
use PropertyAgent\Models\Http\ServerRequest;
use PropertyAgent\Models\Http\Response;
use PropertyAgent\Models\Http\Stream;
use PropertyAgent\Models\ServerRequestFactory;
use PropertyAgent\Repositories\PropertiesRepository;
use PropertyAgent\Models\Utilities;
use PropertyAgent\Models\Auth;

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
        return $this->status(501);
    }

    /**
    * @todo
    */
    public function getProperties()
    {
        return $this->status(501);
    }

    /**
    * @todo
    */
    public function createProperty()
    {
        if (!Auth::hasRole('admin', 'superadmin')) {
            return $this->text('', 403);
        }

        $body = $this->request->getParsedBody();
        $files = $this->request->getUploadedFiles();
        $filePaths = array();
        $fileSources = array();

        if (empty($body) || empty($files)) {
            return $this->text('', 400);
        }

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
                return $this->text($mimeType, 415);
            }

            $fileName = Utilities::createUniqId().$fileType;
            $filePaths[] = '/app/PropertyAgent/Images/'.$fileName;

            $fileSources[] = array(
                'path' => __DIR__.'/../Images/'.$fileName,
                'file' => $file
            );
        }

        $body['images'] = $filePaths;
        $body['map'] = 'map';

        if ($this->repository->create($body, null, true)) {
            foreach ($fileSources as $file) {
                $file['file']->moveTo($file['path']);
            }

            return $this->text('', 201);
        }

        return $this->text('', 400);
    }

    /**
    * @todo
    */
    public function updateProperty()
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
    public function deleteProperty()
    {
        if (!Auth::hasRole('admin', 'superadmin')) {
            return $this->text('', 403);
        }

        $params = $this->request->getAttribute('routeParams');

        if ($this->repository->delete($params['id'])) {
            return $this->text('', 204);
        }

        return $this->text('', 404);
    }
}