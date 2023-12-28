<?php

declare(strict_types=1);

use GuzzleHttp\Client;
use LionHelpers\Arr;
use LionHelpers\Str;
use LionRequest\Request;
use LionRequest\Response;

/**
 * -----------------------------------------------------------------------------
 * framework level predefined constants
 * -----------------------------------------------------------------------------
 **/

define('client', new Client());
define('request', (new Request())->capture());
define('response', new Response());
define('env', (object) $_ENV);
define('str', new Str());
define('arr', new Arr());
