<?php

namespace App\Game\Pieces;

use App\Util\Position;

class Rook extends AbstractBlockable
{
    use MovesStraightTrait;
    use HasMovedTrait; // necessary for Castling move

    protected static string $name = 'Rook';

    public function checkDefaultMove(Position $position): bool
    {
        return $this->straightMoveIsCorrect($this->getPosition(), $position);
    }

    public function moveToPosition(Position $position): void
    {
        parent::moveToPosition($position);
        $this->setMovedState(true); // necessary for Castling move
    }
}