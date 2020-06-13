<?php

namespace App\Game\Pieces;

use App\Exceptions\Game\GameRuleException;
use App\Exceptions\Game\InvalidMoveException;
use App\Exceptions\Http\HttpRequestException;
use App\Util\Position;

class Pawn extends Piece
{
    private const TRANSFORMATIONS = ['Rook', 'Queen', 'Bishop', 'Knight'];
    protected static string $name = 'Pawn';
    private int $direction;
    private int $yStart; // start y position
    private int $yFinish; // finish y position

    public function __construct(string $color)
    {
        parent::__construct($color);

        if ($this->color === COLOR_WHITE) {
            $this->direction = DIRECTION_POSITIVE;
            $this->yStart = 1;
            $this->yFinish = 7;
        } else {
            $this->direction = DIRECTION_NEGATIVE;
            $this->yStart = 6;
            $this->yFinish = 0;
        }
    }

    public function canCapturePosition(Position $position): bool
    {
        return $this->checkDiagonalMove($position) || $this->checkEnPassantMove($position);
    }

    private function checkDiagonalMove(Position $position): bool
    {
        return $this->positionIsOccupiedByOpponent($position) &&
            $position->y === $this->y() + 1 * $this->direction &&
            abs($position->x - $this->x()) === 1;
    }

    private function checkEnPassantMove(Position $position): bool
    {
        return $this->getChessboard()->enPassantPositionExists() &&
               $position == $this->getChessboard()->getEnPassantPosition() &&
               $position->y === $this->y() &&
               abs($position->x - $this->x()) === 1;
    }

    /**
     * @param Position $position
     * @throws GameRuleException
     * @throws HttpRequestException
     * @throws InvalidMoveException
     */
    public function tryMoveToPosition(Position $position): void
    {
        $firstCellIsEmpty = $this->positionIsFree($this->getPosition()->add(0, 1 * $this->direction));

        // Forward move
        if ($this->x() === $position->x && $firstCellIsEmpty) {
            if ($this->checkDefaultMove($position)) {
                $this->moveToPosition($position);
                return;
            }

            if ($this->checkDoubleForwardMove($position)) {
                $this->getChessboard()->setNewEnPassantPosition($position);
                $this->moveToPosition($position);
                return;
            }
        }

        if ($this->checkDiagonalMove($position)) {
            $this->capturePosition($position);
            return;
        }

        $enPassantPosition = $position->add(0, -1 * $this->direction);

        if ($this->checkEnPassantMove($enPassantPosition)) {
            $this->getChessboard()->removePieceFromBoard($enPassantPosition);
            $this->moveToPosition($position);
            return;
        }

        throw new InvalidMoveException(
            'Invalid move for piece [' . static::getName() . '] on position ' . $this->getPosition()
        );
    }

    public function checkDefaultMove(Position $position): bool
    {
        return $position->y === $this->y() + 1 * $this->direction;
    }

    /**
     * @param Position $position
     * @throws GameRuleException
     * @throws InvalidMoveException
     * @throws HttpRequestException
     */
    public function moveToPosition(Position $position): void
    {
        parent::moveToPosition($position);

        if ($this->canTransform($position))
            $this->transform(request('piece'));
    }

    private function canTransform(Position $position): bool
    {
        return ($position->y === $this->yFinish);
    }

    /**
     * @param string $pieceName
     * @throws GameRuleException
     * @throws InvalidMoveException
     */
    private function transform(string $pieceName): void
    {
        if (!in_array($pieceName, self::TRANSFORMATIONS))
            throw new GameRuleException('Error: Pawn can not be transformed into [' . $pieceName . ']');

        $pieceClass = "App\\Game\\Pieces\\$pieceName";
        $piece = new $pieceClass($this->color);

        $this->getChessboard()->removePieceFromBoard($this->getPosition());
        $this->getChessboard()->placePieceOnBoard($this->getPosition(), $piece);
    }

    private function checkDoubleForwardMove(Position $position): bool
    {
        return $position->y === $this->y() + 2 * $this->direction &&
               $this->positionIsFree($position) &&
               $this->getPosition()->y === $this->yStart;
    }
}