<?php

namespace App;

use App\Exceptions\Http\HttpNotFoundException;
use App\Game\Game;
use App\Http\JsonResponse;

class Route
{
    /**
     * Route to specified action
     * @return JsonResponse
     * @throws HttpNotFoundException
     */
    public static function start(): JsonResponse
    {
        $uri = $_SERVER['REQUEST_URI'];

        $action = self::getAction($uri);
        $game = new Game();

        if (!method_exists($game, $action))
            throw new HttpNotFoundException('Not found');

        return $game->$action();
    }

    /**
     * Get action name from the given URI
     * @param string $uri
     * @return string
     * @throws HttpNotFoundException
     */
    private static function getAction(string $uri): string
    {
        $apiBasePath = addcslashes(config('API_BASE_PATH'), '/');

        $regExp = "/^\/$apiBasePath\/([a-z]+)$/";
        preg_match($regExp, $uri,$matches);

        if (!(count($matches) > 1)) throw new HttpNotFoundException('Not found');

        return $matches[1];
    }
}