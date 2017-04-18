<?php 
namespace PropertyAgent\Models;

use ReflectionClass;
use PropertyAgent\Models\Http\ServerRequest;
use PropertyAgent\Models\Http\Response;
use PropertyAgent\Models\Http\Stream;
use PropertyAgent\Models\ControllerTrait;
use PropertyAgent\Models\Utilities;

/**
* Defines the application which holds the incoming request from the client, routes the request
* to the appropiate controller based on the Uri path and then returns a response based an the 
* data which is received in the request.
*/
class Application
{
    /**
    * The server request which is created from the PHP super globals.
    *
    * @var ServerRequest
    */
    public $request;
    
    /**
    * The response that will be returned to the user. This will be manipulated by the controllers 
    * and reassigned because its methods clones the instance to enforce immutability.
    *
    * @var Response
    */
    protected $response;
    
    /**
    * The routes which holds a regular expression for matching the incoming request path to a route 
    * and the instantiate an instance of the controller which is associated with that route.
    *
    * @var array
    */
    public $routes = array();
    
    /**
    * Holds the registered controllers and their full namespace so that they can be instantiated at 
    * runtime based on their associate route.
    *
    * @var array
    */
    public $controllers = array();

    /**
    * Constructs the application instance and instantiate the incoming request from the server request 
    * factory. The response object is instantiated as a fresh copy without any information.
    */
    public function __construct()
    {
        //ini_set('display_errors', false);
        ini_set('default_mimetype', '');

        $this->request = ServerRequestFactory::getServerRequest();
        $this->response = new Response();
    }

    /**
    * Registers a route, its associated controller and the method which is to be called if the route 
    * has been matched on an incoming request.
    *
    * @param string $method The incoming request method e.g. GET, POST, PUT or DELETE.
    * @param string $path The path is a regular expression.
    * @param string $controllerClass Key to the namespace of the controller.
    * @param string $controllerMethod The method which should be called from the controller.
    */
    public function registerRoute($method, $path, $controllerClass, $controllerMethod)
    {
        $this->routes[strtoupper($method)][$path] = array(
            'class' => $controllerClass,
            'method' => $controllerMethod
        );
    }

    /**
    * Registers a controller by a key with the full namespace of the controller. The key $controllerClass will be 
    * used from the registered route to match the namespace which is registered for the controller.
    *
    * @param $controllerClass
    * @param $controllerNamespace The full namespace of the controller e.g. Realtor\Controllers\UsersController
    */
    public function registerController($controllerClass, $controllerNamespace)
    {
        $this->controllers[$controllerClass] = $controllerNamespace;
    }

    /**
    * Retrieves or creates a new instance of the passed controller class name. This is to ensure that the same 
    * instance of the controller will be used for each registered route which uses the same controller. This method 
    * will construct the new controller with the request and response passed as parameters.
    *
    * @param string $controllerClass The full namespace of the controller.
    */
    private function getController($controllerClass)
    {
        if (!isset($this->controllers[$controllerClass])) {            
            $reflection = new ReflectionClass($controllerClass);

            $controller = $reflection
                ->newInstanceArgs(array(
                    $this->request,
                    $this->response
                ));
            
            $this->controllers[$controllerClass] = $controller;
        }

        if ($this->controllers[$controllerClass] instanceof ControllerTrait) {
            return $this->controllers[$controllerClass];
        }
    }

    /**
    * Sends the headers from the response to the client and writes the response body to the 
    * output stream.
    */
    protected function respond()
    {
        $statusCode = $this->response->getStatusCode();

        //Don't resend headers if they've already been sent
        if (!headers_sent()) {
            header(sprintf(
                'HTTP/%s %s %s',
                $this->response
                    ->getProtocolVersion(),
                $statusCode,
                $this->response
                    ->getReasonPhrase()
            ), true, $statusCode);

            $headers = $this->response
                ->getHeaders();

            //Set each individual header of the response if any exists.
            if (!empty($headers)) {
                foreach ($this->response->getHeaders() as $key => $values) {
                    foreach ($values as $value) {
                        header(sprintf(
                            '%s: %s',
                            $key,
                            $value
                        ), false);
                    }
                }
            }
        }

        $responseSize = $this->response
            ->getBody()
            ->getSize();

        if ($responseSize !== null) {
            $response = $this->response
                ->withHeader('Content-Length', $responseSize.'');

            $body = $this->response
                ->getBody();

            if ($body->isSeekable()) {
                $body->rewind();
            }

            /*
            Notice that print and echo writes to the output stream and so there's no 
            point in opening the stream to write the contents myself.
            */
            while (!$body->eof()) {
                echo $body->read(Stream::CHUNK_SIZE);

                /*
                If the connection status to the client is anythin but normal then stop 
                writing data to the output stream
                */
                if (connection_status() != CONNECTION_NORMAL) {
                    break;
                }
            }
        }
    }

    /**
    * Runs the application. The incoming request path will be matched against the registered routes 
    * and the controller associated with that route will be instantiated and have the chosen method 
    * called.
    */
    public function run()
    {
        $requestMethod = $this->request
            ->getMethod();

        $requestPath = $this->request
            ->getUri()
            ->getPath();


        //If no routes with the request method exists then return 404 to the client
        if (!isset($this->routes[$requestMethod])) {
            $this->setResponseStatus(404);
            $this->respond();
            return;
        }

        foreach ($this->routes[$requestMethod] as $pathRegex => $pathAction) {
            if ((preg_match($pathRegex, $requestPath, $pathMatches)) === 1) {
                $this->request = $this->request
                    ->withAttribute('routeParams', $pathMatches);

                $controllerClass = $pathAction['class'];
                
                //Return 404 if controller class could not be found
                if (!isset($this->controllers[$controllerClass])) {
                    $this->setResponseStatus(404);
                    $this->respond();
                    return;
                }

                $controllerClass = $this->controllers[$controllerClass];
                $controllerMethod = $pathAction['method'];

                //Return 500 if method could not be found in controller class
                if (!method_exists($controllerClass, $controllerMethod)) {
                    $this->setResponseStatus(500);
                    $this->respond();
                    return;
                }

                $controller = $this->getController($controllerClass);

                $response = call_user_func(array(
                    $controller,
                    $controllerMethod
                ));

                //Return 500 if call to the controller class method fails or the method doesn't return an instance of Response
                if ($response === false || !($response instanceof Response)) {
                    $this->setResponseStatus(500);
                    $this->respond();
                    return;
                }

                $this->response = $response;
                $this->respond();
                return;
            }
        }

        $this->setResponseStatus(404);
        $this->respond();
    }

    /**
    * Clones the existing instance of response with a new status code and reassign it 
    * to the member variable.
    */
    private function setResponseStatus($statusCode)
    {
        $this->response = $this->response
            ->withHeader('Content-Type', 'text/plain')
            ->withStatus($statusCode);
    }
}