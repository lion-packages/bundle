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
 */
const client = new Client();

/**
 * [Object with properties captured in an HTTP request]
 */
define('request', (new Request())->capture());

/**
 * [Object of Response class to generate response objects]
 *
 * @var Response
 */
const response = new Response();

/**
 * Object of class Str
 *
 * @var Str
 */
const str = new Str();

/**
 * Object of class Arr
 *
 * @var Arr
 */
const arr = new Arr();

/**
 * [Defines a null value]
 *
 * @var null
 */
const NULL_VALUE = null;
