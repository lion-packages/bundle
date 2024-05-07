<?php

declare(strict_types=1);

header('Content-Type: application/json');

require_once(__DIR__ . '/../vendor/autoload.php');

use Lion\Bundle\Helpers\ExceptionCore;
use Lion\Bundle\Kernel\HttpKernel;
use Lion\Dependency\Injection\Container;

(new ExceptionCore)->exceptionHandler();

include_once(__DIR__ . '/../routes/rules.php');

((new Container())->injectDependencies(new HttpKernel()))->validateRules();

include_once(__DIR__ . '/../routes/middleware.php');
include_once(__DIR__ . '/../routes/web.php');
