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

/**
 * @var Client client
 * */
define('client', new Client());

/**
 * @var object request
 * */
define('request', (new Request())->capture());

/**
 * @var Response response
 * */
define('response', new Response());

/**
 * @var object env
 * */
define('env', (object) $_ENV);

/**
 * @var Str str
 * */
define('str', new Str());

/**
 * @var Arr arr
 * */
define('arr', new Arr());
