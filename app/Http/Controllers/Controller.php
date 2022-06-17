<?php

namespace App\Http\Controllers;

use LionMailer\Mailer;
use LionRequest\{ Request, Json, Response };
use LionSecurity\RSA;

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
        RSA::$url_path = $this->env->RSA_URL_PATH === '' ? RSA::$url_path : "../{$this->env->RSA_URL_PATH}";

        Mailer::init([
            'info' => [
                'debug' => (int) $this->env->MAIL_DEBUG,
                'host' => $this->env->MAIL_HOST,
                'port' => (int) $this->env->MAIL_PORT,
                'email' => $this->env->MAIL_EMAIL,
                'password' => $this->env->MAIL_PASSWORD,
                'user_name' => $this->env->MAIL_USER_NAME,
                'encryption' => $this->env->MAIL_ENCRYPTION === 'false' ? false : ($this->env->MAIL_ENCRYPTION === 'true' ? true : false)
            ]
        ]);
    }

}