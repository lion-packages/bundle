<?php

/**
 * ------------------------------------------------------------------------------
 * Console Routes
 * ------------------------------------------------------------------------------
 * This is where you can record your commands for your application
 * ------------------------------------------------------------------------------
 **/

return [
    App\Console\RSACommand::class,
    App\Console\GenerateJWTCommand::class,
    App\Console\RouteListCommand::class
];