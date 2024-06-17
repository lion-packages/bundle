<?php

declare(strict_types=1);

header('Content-Type: application/json');

require_once(__DIR__ . '/../vendor/autoload.php');

define('LION_START', microtime(true));

define('IS_INDEX', false);

(new Lion\Exceptions\Serialize())->exceptionHandler();

include_once(__DIR__ . '/../routes/middleware.php');
include_once(__DIR__ . '/../routes/web.php');
