<?php

/**
 * Constants used across the application
 *
 * This file defines global constants that are initialized with specific class
 * instances or data objects for reuse
 */

declare(strict_types=1);

use Lion\Helpers\Arr;
use Lion\Helpers\Str;
use Lion\Request\Request;
use Lion\Request\Response;

/**
 * [Object with properties captured in an HTTP request]
 */
define('REQUEST', (new Request())->capture());

/**
 * [Object of Response class to generate response objects]
 *
 * @var Response
 */
const RESPONSE = new Response();

/**
 * Object of class Str
 *
 * @var Str
 */
const STR = new Str();

/**
 * Object of class Arr
 *
 * @var Arr
 */
const ARR = new Arr();
