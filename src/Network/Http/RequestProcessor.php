<?php

namespace Zan\Framework\Network\Http;

use Zan\Framework\Foundation\Coroutine\Task;
use Zan\Framework\Foundation\Domain\Controller;
use Zan\Framework\Network\Http\Exception\InvalidRoute;

class RequestProcessor {

    private $request;
    private $response;

    private $controllerNamespace = 'Zanhttp';

    public function __construct(Request $request, Response $response)
    {
        $this->request  = $request;
        $this->response = $response;
    }

    public function run($route)
    {
        $controller = $this->createController($route);

        if (!($controller instanceof Controller)) {
            throw new InvalidRoute('Not found controller:'.$controller);
        }
        $action = $route['action'];
        if (!method_exists($controller, $action)) {
            throw new InvalidRoute('Class does not exist method '. get_class($controller).'::'.$action);
        }
        $task = new Task($controller->$action());
        $task->run();
    }

    private function createController($route)
    {
        $module    = ucwords($route['module']);
        $className = ucwords($route['controller']);

        if (!preg_match('%^[A-Z][a-zA-Z][a-z0-9]*$%', $className)) {
            return null;
        }
        $className  = str_replace(' ', '', $className);
        $controller = ltrim($this->controllerNamespace . '\\' . $module . '\\Controller\\'. $className);

        if (!class_exists($controller)) {
            return null;
        }
        return new $controller($this->request, $this->response);
    }

}