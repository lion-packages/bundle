<?php

declare(strict_types=1);

use GuzzleHttp\Client;
use Lion\Helpers\Arr;
use Lion\Helpers\Str;
use Lion\Request\Request;
use Lion\Request\Response;

/**
 * [Object of the GuzzleHttp Client class]
 *
 * @var Client
 * */
define('client', new Client());

/**
 * [Object with properties captured in an HTTP request]
 *
 * @var stdClass
 * */
define('request', (new Request())->capture());

/**
 * [Object of Response class to generate response objects]
 *
 * @var Response
 * */
define('response', new Response());

/**
 * Object of class Str
 *
 * @var Str
 * */
define('str', new Str());

/**
 * Object of class Arr
 *
 * @var Arr
 * */
define('arr', new Arr());
