<?php

namespace App\Game\Pieces;

use App\Exceptions\Game\InvalidMoveException;
use App\Game\Chessboard;
use App\Util\Position;
use App\Game\Cell;

abstract class Piece
{
    protected static string $name;

    protected string $color;
    protected Cell $cell;

    public function __construct(string $color)
    {
        $this->color = $color;
    }

    public function getColor(): string
    {
        return $this->color;
    }

    public function getCell(): Cell
    {
        return $this->cell;
    }

    public function setCell(Cell $cell): void
    {
        $this->cell = $cell;
    }

    public function hasColor(string $color): bool
    {
        return $this->color === $color;
    }

    public function x(): int
    {
        return $this->getPosition()->x;
    }

    public function getPosition(): Position
    {
        return $this->cell->getPosition();
    }

    public function y(): int
    {
        return $this->getPosition()->y;
    }

    public function canCapturePosition(Position $position): bool
    {
        return $this->checkDefaultMove($position);
    }

    public abstract function checkDefaultMove(Position $position): bool;

    protected function positionIsFree(Position $position): bool
    {
        return $this->getChessboard()->getCell($position)->isEmpty();
    }

    public function getChessboard(): Chessboard
    {
        return Cell::getChessboard();
    }

    protected function positionIsOccupiedByOpponent(Position $position): bool
    {
        return $this->getChessboard()->getCell($position)->isOccupiedByColor($this->oppositeColor());
    }

    public function oppositeColor(): string
    {
        return oppositeColor($this->color);
    }

    /**
     * @param Position $position
     * @throws InvalidMoveException
     */
    public function tryMoveToPosition(Position $position): void
    {
        if ($this->positionIsFree($position)) {
            $this->moveToPosition($position);
            return;
        }

        if ($this->positionIsOccupiedByOpponent($position)) {
            $this->capturePosition($position);
            return;
        }

        throw new InvalidMoveException(
            'Invalid move for piece [' . static::getName() . '] on position ' . $this->getPosition()
        );
    }

    protected function moveToPosition(Position $position): void
    {
        $this->cell->removePieceFromCell();
        $this->getChessboard()->getCell($position)->placePieceOnCell($this);
    }

    /**
     * @param Position $position
     * @throws InvalidMoveException
     */
    protected function capturePosition(Position $position): void
    {
        $this->getChessboard()->removePieceFromBoard($position);
        $this->moveToPosition($position);
    }

    public static function getName(): string
    {
        return static::$name;
    }
}
