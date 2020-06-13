<?php

namespace App\Game\Pieces;

use App\Util\Position;

class Bishop extends AbstractBlockable
{
    use MovesDiagonallyTrait;

    protected static string $name = 'Bishop';

    public function checkDefaultMove(Position $position): bool
    {
        return $this->diagonalMoveIsCorrect($this->getPosition(), $position);
    }
}