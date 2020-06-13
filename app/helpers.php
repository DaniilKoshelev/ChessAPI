<?php

use App\Http\JsonRequest;

/**
 * Send 404 error
 */
function sendNotFoundResponse(): void
{
    $host = 'http://' . $_SERVER['HTTP_HOST'] . '/';
    header('HTTP/1.1 404 Not Found');
    header("Status: 404 Not Found");
    header('Location:' . $host . '404');
}

function sendJsonResponse($response): void
{
    header('Content-Type: application/json');

    echo $response;
}

/**
 * Get the given option from request if option is provided.
 * Otherwise get the request instance.
 * @param string $option
 * @return string | JsonRequest
 * @throws \App\Exceptions\Http\HttpRequestException
 */
function request(string $option = '')
{
    global $request;

    if (!$option)
        return $request;

    return $request->get($option);
}

/**
 * Return the given config option
 * @param $option
 * @return string
 */
function config(string $option): string
{
    global $config;

    return $config->get($option);
}

/**
 * Return the given env option
 * @param string $option
 * @return string
 */
function env(string $option): string
{
    return $_ENV[$option];
}

/**
 * determine direction
 * @param int $x1
 * @param int $x2
 * @return int
 */
function direction(int $x1, int $x2): int
{
    return ($x1 > $x2) ? DIRECTION_POSITIVE : (($x1 < $x2) ? DIRECTION_NEGATIVE : DIRECTION_NEUTRAL);
}

function oppositeColor(string $color): string
{
    return ($color === COLOR_WHITE) ? COLOR_BLACK : COLOR_WHITE;
}