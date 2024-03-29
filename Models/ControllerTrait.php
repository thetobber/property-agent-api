<?php
namespace PropertyAgent\Models;

use InvalidArgumentException;
use PropertyAgent\Models\Http\ServerRequest;
use PropertyAgent\Models\Http\Response;

/**
* Defines the basic need of a controller class and a few helpful 
* methods for returning a response. The request and response 
* object is injected into class upon instantiation if the route 
* which is associated with this controller is used.
*/
abstract class ControllerTrait
{
    /**
    * The ServerRequest object which is passed when the controller is called.
    *
    * @var ServerRequest
    */
    protected $request;

    /**
    * The Response object which is passed when the controller is called.
    *
    * @var ServerRequest
    */
    protected $response;

    /**
    * The parameters parsed from the route variables.
    *
    * @var array
    */
    protected $params;

    public function __construct(ServerRequest $request, Response $response)
    {
        $this->request = $request;
        $this->response = $response;
        $this->params = $request->getAttribute('routeParams');
    }

    /**
    * Encodes an array or object to json and writes it to the response body.
    * The content type of the response is set to application/json.
    *
    * @param array|object $contents Data which is encoded and written to the response body.
    * @param int $statusCode Status code of the response.
    * @return Response Returns a cloned instance of the response object passed upon
    *   instantiation of this class with a new body and status code.
    * @throws InvalidArgumentException if the argument $contents is not an array or object.
    */
    protected function json($contents, $statusCode = 200)
    {
        if (is_array($contents) || is_object($contents)) {
            $encoded = json_encode(
                (array) $contents,
                JSON_BIGINT_AS_STRING
            );

            return $this->writeResponse(
                'application/json',
                $encoded,
                $statusCode
            );
        }

        throw new InvalidArgumentException('Argument $contents must be an array or object.');
    }

    /**
    * Writes a string to the response body and sets the content type to text/plain.
    *
    * @param string $contents The string written to the response body.
    * @param int $statusCode Status code of the response.
    * @return Response Returns a cloned instance of the response object passed upon
    *   instantiation of this class with a new body and status code.
    * @throws InvalidArgumentException if the argument $contents is not a string.
    */
    protected function text($contents, $statusCode = 200)
    {
        if (is_string($contents)) {
            return $this->writeResponse(
                'text/plain',
                $contents,
                $statusCode
            );
        }

        throw new InvalidArgumentException('Argument $contents must be a string.');
    }

    /**
    * Writes a string to the response body and sets the content type to text/html.
    *
    * @param string $contents The string written to the response body.
    * @param int $statusCode Status code of the response.
    * @return Response Returns a cloned instance of the response object passed upon
    *   instantiation of this class with a new body and status code.
    * @throws InvalidArgumentException if the argument $contents is not a string.
    */
    protected function html($contents, $statusCode = 200)
    {
        if (is_string($contents)) {
            return $this->writeResponse(
                'text/html',
                $contents,
                $statusCode
            );
        }

        throw new InvalidArgumentException('Argument $contents must be a string.');
    }

    /**
    * Return a response with an empty body and a status code only.
    *
    * @param int $statusCode Status code of the response.
    * @return Response Returns a cloned instance of the response object passed upon
    *   instantiation of this class with a new body and status code.
    */
    protected function status($statusCode = 200)
    {
        if (!isset(Response::STATUS_CODES[$statusCode])) {
            $statusCode = 500;
        }

        $this->response = $this->response
            ->withHeader('Content-Type', 'text/plain')
            ->withStatus($statusCode);

        return $this->response;
    }

    /**
    * Clones the response passed upon instantiation of this class with a new content 
    * type header and status code. The member variable $response is reassigned with the 
    * new instance and then the data is written to the body of the response object.
    *
    * @param string $mimeType The media type describing the body of the response object.
    * @param string $contents The data written to the body of the response.
    * @param int $statusCode The status code of the response object.
    * @return Response Returns the newly assigned clone of the response.
    */
    private function writeResponse($mimeType, $contents, $statusCode) {
        $this->response = $this->response
            ->withHeader('Content-Type', $mimeType)
            ->withStatus($statusCode);

        $this->response
            ->getBody()
            ->write($contents);

        return $this->response;
    }
}