<?php

namespace App\Game;

use App\Exceptions\Game\InvalidMoveException;
use App\Game\Pieces\King;
use App\Game\Pieces\Piece;
use App\Game\Pieces\Rook;
use App\Util\Position;
use JsonSerializable;
use SplObjectStorage;

class Cell implements JsonSerializable
{
    private static Chessboard $chessboard;

    private Position $position;
    private Piece $piece;

    public function __construct(Position $position)
    {
        $this->position = $position;
    }

    public static function setChessboard(Chessboard $chessboard): void
    {
        self::$chessboard = $chessboard;
    }

    public static function getChessboard(): Chessboard
    {
        return self::$chessboard;
    }

    public function placePieceOnCell(Piece $piece): void
    {
        $this->piece = $piece;
        $this->piece->setCell($this);
    }

    /**
     * @return Piece
     * @throws InvalidMoveException
     */
    public function getPiece(): Piece
    {
        if ($this->isEmpty())
            throw new InvalidMoveException('No piece on position: ' . $this->position);

        return $this->piece;
    }

    public function removePieceFromCell(): void
    {
        unset($this->piece);
    }

    public function isEmpty(): bool
    {
        return !isset($this->piece);
    }

    public function getPosition(): Position
    {
        return $this->position;
    }

    public function canBeCapturedByPieces(SplObjectStorage $pieces): bool
    {
        foreach ($pieces as $piece)
            if ($piece->canCapturePosition($this->getPosition()))
                return true;

        return false;
    }

    public function isOccupiedByColor(string $color): bool
    {
        return !$this->isEmpty() && $this->piece->hasColor($color);
    }

    public function jsonSerialize(): array
    {
        $data = [
            'piece' => $this->piece::getName(),
            'color' => $this->piece->getColor(),
            'position' => $this->position
        ];

        if ($this->piece instanceof Rook || $this->piece instanceof King)
            $data['hasMoved'] = $this->piece->getMovedState();

        return $data;
    }
}