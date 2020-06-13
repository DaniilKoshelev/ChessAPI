<?php

namespace App\Game\Pieces;

use App\Util\Position;

trait MovesDiagonallyTrait
{
    public function diagonalMoveIsCorrect(Position $from, Position $to): bool
    {
        return abs($from->x - $to->x) === abs($from->y - $to->y);
    }
}