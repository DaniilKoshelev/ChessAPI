<?php

namespace App\Game\Pieces;

trait HasMovedTrait
{
    /*
    | This trait is used to track whether current piece has moved or not.
    | It is required to implement the Castling move
    */
    private bool $hasMoved = false;

    public function setMovedState(bool $state): void
    {
        $this->hasMoved = $state;
    }

    public function getMovedState(): bool
    {
        return $this->hasMoved;
    }
}