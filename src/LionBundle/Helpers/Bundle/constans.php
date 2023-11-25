<?php

declare(strict_types=1);

/**
 * -----------------------------------------------------------------------------
 * framework level predefined constants
 * -----------------------------------------------------------------------------
 **/

define('client', new \GuzzleHttp\Client());
define('request', (new \LionRequest\Request())->capture());
define('response', new LionRequest\Response());
define('env', (object) $_ENV);
define('str', new \LionHelpers\Str());
define('arr', new \LionHelpers\Arr());
