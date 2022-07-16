<?php

namespace App\Http\Controllers;

use LionRequest\{ Request, Json, Response };

class Controller {

    protected object $request;
    protected object $env;
    protected Json $json;
    protected Response $response;

    private function __construct() {

    }

    protected function init(): void {
        $this->env = Request::getInstance()->env();
        $this->request = Request::getInstance()->request();
        $this->json = Json::getInstance();
        $this->response = Response::getInstance();
    }

}