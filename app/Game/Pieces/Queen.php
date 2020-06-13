<?php

namespace App\Game\Pieces;

use App\Util\Position;

class Queen extends AbstractBlockable
{
    use MovesDiagonallyTrait;
    use MovesStraightTrait;

    protected static string $name = 'Queen';

    public function checkDefaultMove(Position $position): bool
    {
        return $this->diagonalMoveIsCorrect($this->getPosition(), $position) ||
               $this->straightMoveIsCorrect($this->getPosition(), $position);
    }
}