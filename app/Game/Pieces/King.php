<?php

namespace App\Game\Pieces;

use App\Exceptions\Game\InvalidMoveException;
use App\Util\Position;

class King extends Piece
{
    use HasMovedTrait; // necessary for Castling move

    protected static string $name = 'King';

    /**
     * @param Position $position
     * @throws InvalidMoveException
     */
    public function tryMoveToPosition(Position $position): void
    {
        if ($this->checkDefaultMove($position) && $this->positionIsSafe($position)) {
            parent::tryMoveToPosition($position);
            return;
        }

        if ($this->castlingMove($position))
            return;

        throw new InvalidMoveException(
            'Invalid move for piece [' . static::getName() . '] on position ' . $this->getPosition()
        );
    }

    public function checkDefaultMove(Position $to): bool
    {
        return abs($this->x() - $to->x) <= 1 && abs($this->y() - $to->y) <= 1;
    }

    /**
     * The given position can not be captured by the opponent's pieces
     * @param Position $position
     * @return bool
     */
    public function positionIsSafe(Position $position): bool
    {
        $opponentPieces = $this->getChessboard()->getPiecesOfColor($this->oppositeColor());
        return !$this->getChessboard()->getCell($position)->canBeCapturedByPieces($opponentPieces);
    }

    /**
     * Make Castling move
     * @param Position $to
     * @return bool
     * @throws InvalidMoveException
     */
    public function castlingMove(Position $to): bool
    {
        $dir = direction($to->x, $this->x());

        // Define if current move is Castling move
        if ($to != $this->getPosition()->add(2 * $dir, 0))
            return false;

        // King can not move before Castling move
        if ($this->getMovedState())
            return false;

        $rookStartX = ($dir === DIRECTION_NEGATIVE) ? 0 : 7;
        $rookFinishX = ($dir === DIRECTION_NEGATIVE) ? 3 : 5;
        $rookY = ($this->color === COLOR_WHITE) ? 0 : 7;
        $rookCell = $this->getChessboard()->getCell(new Position($rookStartX, $rookY));

        if ($rookCell->isEmpty())
            return false;

        $rook = $rookCell->getPiece();

        if (!$rook instanceof Rook)
            return false;

        // Rook can not move before Castling move
        if ($rook->getMovedState())
            return false;

        // Assure there are no blocking pieces in between
        if ($rook->foundBlockingPieces($this->getPosition()))
            return false;

        // Assure that King's way is safe
        $positionToCheck = clone $this->getPosition();
        $end = $this->getPosition()->add(3 * $dir, 0);

        while ($positionToCheck != $end) {
            if (!$this->positionIsSafe($positionToCheck))
                return false;

            $positionToCheck->move(1 * $dir, 0);
        }

        // Moving pieces
        $rookCell->removePieceFromCell();
        $this->getChessboard()->getCell(new Position($rookFinishX, $rookY))->placePieceOnCell($rook);
        $rook->setMovedState(true);
        $this->moveToPosition($to);

        return true;
    }

    public function moveToPosition(Position $position): void
    {
        parent::moveToPosition($position);
        $this->setMovedState(true); // necessary for Castling move
    }

    public function canEscape(): bool
    {
        $possibleMoves = [[0, 1], [1, 1], [1, 0], [-1, 0], [0, -1], [-1, -1], [1, -1], [-1, 1]];

        foreach ($possibleMoves as $move) {
            $newPosition = $this->getPosition()->add($move[0], $move[1]);

            if (!$newPosition->isValid())
                continue;

            if (($this->positionIsOccupiedByOpponent($newPosition) || $this->positionIsFree($newPosition)) &&
                $this->positionIsSafe($newPosition))
                return true;
        }

        return false;
    }
}