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
 * Defines the 'code' property of response objects.
 */

const CODE = 'code';

/**
 * Defines the 'status' property of response objects.
 */

const STATUS = 'status';

/**
 * Defines the 'message' property of response objects.
 */

const MESSSAGE = 'message';

/**
 * Defines the 'data' property of response objects.
 */

const DATA = 'data';

/**
 * Get all values sent via an HTTP request.
 */
define('REQUEST', new Request()->capture());

/**
 * Allows you to manage custom or already defined response objects.
 *
 * @var Response
 */
const RESPONSE = new Response();

/**
 * Modify and construct strings with different formats.
 *
 * @var Str
 */
const STR = new Str();

/**
 * Modify and build arrays with different indexes or values.
 *
 * @var Arr
 */
const ARR = new Arr();
