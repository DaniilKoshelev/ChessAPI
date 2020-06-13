<?php

/*
|--------------------------------------------------------------------------
| Entry point for all API requests
|--------------------------------------------------------------------------
*/

declare(strict_types = 1);

error_reporting(E_ALL);

// Register the Auto Loader
require_once __DIR__ . '/vendor/autoload.php';

// Register constants
require_once __DIR__ . '/app/constants.php';

// Register helper functions
require_once __DIR__ . '/app/helpers.php';

// Bootstrap the application
require_once __DIR__ . '/bootstrap/app.php';
