<?php

namespace App\Game\Pieces;

use App\Util\Position;

trait MovesStraightTrait
{
    public function straightMoveIsCorrect(Position $from, Position $to): bool
    {
        return ($from->x - $to->x) * ($from->y - $to->y) === 0;
    }
}