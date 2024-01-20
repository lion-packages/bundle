<?php

declare(strict_types=1);

use GuzzleHttp\Client;
use Lion\Helpers\Arr;
use Lion\Helpers\Str;
use Lion\Request\Request;
use Lion\Request\Response;

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
