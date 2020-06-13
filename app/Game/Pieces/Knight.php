<?php

namespace App\Game\Pieces;

use App\Exceptions\Game\InvalidMoveException;
use App\Util\Position;

class Knight extends Piece
{
    protected static string $name = 'Knight';

    public function checkDefaultMove(Position $position): bool
    {
        return abs($this->x() - $position->x) * abs($this->y() - $position->y) === 2;
    }

    public function tryMoveToPosition(Position $position): void
    {
        if ($this->checkDefaultMove($position)) {
            parent::tryMoveToPosition($position);
            return;
        }

        throw new InvalidMoveException(
            'Invalid move for piece [' . static::getName() . '] on position ' . $this->getPosition()
        );
    }
}