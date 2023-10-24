<?php

declare(strict_types=1);

namespace App\Traits\Framework;

use App\Http\Kernel;
use Dotenv\Dotenv;
use LionDatabase\Driver;
use LionMailer\MailService;
use LionRequest\Request;
use LionRoute\Request as LionRouteRequest;
use LionRoute\Route;

trait Index
{
    public function loadDotEnv(string $path): void
    {
        (Dotenv::createImmutable($path))->load();
    }

    public function loadCors(array $cors): void
    {
        foreach ($cors as $key => $header) {
            Request::header($key, $header);
        }
    }

    public function loadConnecions(array $connections, array $data = []): void
    {
        Driver::addLog();
        $response_database = Driver::run($connections);

        if (isError($response_database)) {
            logger($response_database->message, 'error', $data);
            finish(error($response_database->message));
        }
    }

    public function loadAccounts(array $accounts): void
    {
        $response_email = MailService::run($accounts);

        if (isError($response_email)) {
            logger($response_email->message, 'error');
            finish(error($response_email->message));
        }
    }

    public function validateRules(array $all_rules): void
    {
        if (isset($all_rules[$_SERVER['REQUEST_METHOD']])) {
            foreach ($all_rules[$_SERVER['REQUEST_METHOD']] as $uri => $rules) {
                if (Kernel::getInstance()->checkUrl($uri)) {
                    foreach ($rules as $key => $rule) {
                        $rule::passes();
                        $rule::display();
                    }
                }
            }
        }
    }

    public function loadRoutes(array $middleware, string $routes): void
    {
        Route::addLog();
        Route::init();
        LionRouteRequest::init(client);
        Route::addMiddleware([...$middleware['framework'], ...$middleware['app']]);
        include_once($routes);
        Route::get('route-list', fn() => Route::getFullRoutes());
        session()->destroy();
        Route::dispatch();
    }
}
