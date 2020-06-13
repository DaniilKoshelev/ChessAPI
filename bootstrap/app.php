<?php

use App\Exceptions\HttpNotFoundException;
use App\Http\JsonRequest;
use App\Util\Config;
use App\Route;

// Initialize .env library
$dotenv = Dotenv\Dotenv::createImmutable('./');
$dotenv->load();

// Initialize config instance
$config = new Config(__DIR__ . '/../config');

// Get JSON request data
$request = new JsonRequest();

// Routing & sending the response
try {
    $response = Route::start();
    sendJsonResponse($response);
} catch (HttpNotFoundException $e) {
    sendNotFoundResponse();
} catch (Exception $e) {
    echo $e->getMessage();
}