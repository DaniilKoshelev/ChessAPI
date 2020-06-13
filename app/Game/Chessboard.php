<?php

namespace App\Game;

use App\Exceptions\Game\GameRuleException;
use App\Exceptions\Game\InternalException;
use App\Exceptions\Game\InvalidMoveException;
use App\Game\Pieces\AbstractBlockable;
use App\Game\Pieces\King;
use App\Game\Pieces\Piece;
use App\Util\Position;
use JsonSerializable;
use SplObjectStorage;

class Chessboard implements JsonSerializable
{
    private array $cells;
    private string $currentPlayerColor;
    private Position $enPassantPosition;
    private Position $newEnPassantPosition;
    private SplObjectStorage $whitePieces;
    private SplObjectStorage $blackPieces;

    public function __construct()
    {
        Cell::setChessboard($this);

        $this->whitePieces = new SplObjectStorage();
        $this->blackPieces = new SplObjectStorage();

        // Creating the board
        for ($x = 0; $x < 8; $x++)
            for ($y = 0; $y < 8; $y++)
                $this->cells[$x][$y] = new Cell(new Position($x, $y));
    }

    public function getEnPassantPosition()
    {
        return isset($this->enPassantPosition) ? $this->enPassantPosition : null;
    }

    public function setEnPassantPosition($position): void
    {
        $this->enPassantPosition = $position;
    }

    public function enPassantPositionExists(): bool
    {
        return isset($this->enPassantPosition);
    }

    public function setNewEnPassantPosition(Position $position): void
    {
        $this->newEnPassantPosition = $position;
    }

    public function setCurrentPlayerColor(string $currentPlayerColor): void
    {
        $this->currentPlayerColor = $currentPlayerColor;
    }

    public function placePieceOnBoard(Position $position, Piece $piece): void
    {
        $this->getPiecesOfColor($piece->getColor())->attach($piece);
        $this->getCell($position)->placePieceOnCell($piece);
    }

    public function getPiecesOfColor(string $color): SplObjectStorage
    {
        $pieces = ($color === COLOR_WHITE) ? $this->whitePieces : $this->blackPieces;

        $pieces->rewind();

        return $pieces;
    }

    public function getCell(Position $position): Cell
    {
        return $this->cells[$position->x][$position->y];
    }

    /**
     * @param Position $position
     * @throws InvalidMoveException
     */
    public function removePieceFromBoard(Position $position): void
    {
        $cell = $this->getCell($position);
        $piece = $cell->getPiece();
        $this->getPiecesOfColor($piece->getColor())->detach($piece);
        $cell->removePieceFromCell();
    }

    /**
     * @param Position $from
     * @param Position $to
     * @throws GameRuleException
     * @throws InvalidMoveException
     */
    public function tryMovePiece(Position $from, Position $to): void
    {
        $piece = $this->getCell($from)->getPiece();

        if (!$this->currentPlayerCanMovePiece($piece))
            throw new GameRuleException(
                'Error: player [' . $this->currentPlayerColor . '] can not move [' . $piece->getColor() . '] pieces'
            );

        $piece->tryMoveToPosition($to);
    }

    private function currentPlayerCanMovePiece(Piece $piece): bool
    {
        return $piece->hasColor($this->currentPlayerColor);
    }

    /**
     * Defining game status for player with given color
     * @param string $color
     * @return string
     * @throws InternalException
     * @throws InvalidMoveException
     */
    public function getStatus(string $color): string
    {
        $king = $this->findKing($color);
        $kingPosition = $king->getPosition();

        if ($king->positionIsSafe($kingPosition))
            return STATUS_PLAYING;

        if ($king->canEscape())
            return STATUS_CHECK;

        // Defining attacking and defending pieces
        $attackingPieces = [];
        $defendingPieces = clone $this->getPiecesOfColor($king->getColor());
        $opponentPieces = $this->getPiecesOfColor($king->oppositeColor());
        $defendingPieces->detach($king);

        foreach ($opponentPieces as $opponentPiece) {
            if ($opponentPiece instanceof AbstractBlockable && $opponentPiece->checkDefaultMove($kingPosition)) {

                // If opponent's piece is Queen | Bishop | Rook, it must be blocked
                $blockingPieces = $opponentPiece->findBlockingPieces($kingPosition, false);
                $blockingPiecesCount = count($blockingPieces);

                if ($blockingPiecesCount === 0)
                    $attackingPieces[] = $opponentPiece;

                if ($blockingPiecesCount !== 1)
                    continue;

                $blockingPiece = $blockingPieces[0];

                if ($blockingPiece->hasSameColor($king))
                    $defendingPieces->detach($blockingPiece);

            } elseif ($opponentPiece->canCapturePosition($kingPosition)) {
                $attackingPieces[] = $opponentPiece;
            }
        }

        // There are more than 1 attacking piece
        if (count($attackingPieces) !== 1)
            return STATUS_MATE;

        // Trying to capture or block opponent's attacking piece using defending pieces
        $attackingPiece = $attackingPieces[0];
        $positionToCapture = clone $attackingPiece->getPosition();

        $xStep = direction($king->x(), $positionToCapture->x);
        $yStep = direction($king->y(), $positionToCapture->y);

        while ($positionToCapture != $kingPosition) {
            if ($this->getCell($positionToCapture)->canBeCapturedByPieces($defendingPieces))
                return STATUS_CHECK;

            $positionToCapture->move($xStep, $yStep);
        }

        return STATUS_MATE;
    }

    /**
     * @param string $color
     * @return King
     * @throws InternalException
     */
    private function findKing(string $color): King
    {
        foreach ($this->getPiecesOfColor($color) as $piece) {
            if ($piece instanceof King)
                return $piece;
        }
        throw new InternalException('Error: could not find [' . $color . '] King on the chessboard');
    }

    public function jsonSerialize(): array
    {
        return [
            'enPassant' => (isset($this->newEnPassantPosition)) ? $this->newEnPassantPosition : null,
            'pieces' => $this->getNonEmptyCells()
        ];
    }

    private function getNonEmptyCells(): array
    {
        $cells = [];
        for ($x = 0; $x < 8; $x++)
            for ($y = 0; $y < 8; $y++) {
                $cell = $this->getCell(new Position($x, $y));
                if (!$cell->isEmpty()) $cells[] = $cell;
            }

        return $cells;
    }
}