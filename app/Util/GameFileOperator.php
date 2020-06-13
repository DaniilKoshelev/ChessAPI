<?php

namespace App\Util;

use App\Game\Chessboard;
use App\Game\Game;
use App\Game\Pieces\King;
use App\Game\Pieces\Rook;

class GameFileOperator
{
    private string $currentPlayerColor;
    private string $status;
    private Chessboard $chessboard;
    private $enPassantPosition;

    public function __construct(Chessboard $chessboard)
    {
        $this->chessboard = $chessboard;
    }

    public function parse(string $filename): void
    {
        $jsonData = json_decode(file_get_contents($filename), true);

        $this->currentPlayerColor = $jsonData['currentPlayerColor'];
        $enPassantPosition = $jsonData['chessboard']['enPassant'];
        $pieces = $jsonData['chessboard']['pieces'];
        $this->status = $jsonData['status'];

        if (!is_null($enPassantPosition))
            $this->enPassantPosition = Position::tryParse($enPassantPosition); // necessary for En Passant move

        foreach ($pieces as $piece) {
            $position = $piece['position'];
            $color = $piece['color'];
            $type = 'App\\Game\\Pieces\\' . $piece['piece'];

            $pieceObject = new $type($color);

            if ($pieceObject instanceof King || $pieceObject instanceof Rook) {
                $hasMoved = $piece['hasMoved'];
                $pieceObject->setMovedState($hasMoved); // necessary for Castling move
            }

            $this->chessboard->placePieceOnBoard(Position::tryParse($position), $pieceObject);
        }
    }

    public function save(Game $game)
    {
        $json = json_encode($game);
        file_put_contents(config('FILE_GAME'), $json);
    }

    public function getCurrentPlayerColor(): string
    {
        return $this->currentPlayerColor;
    }

    public function getStatus(): string
    {
        return $this->status;
    }

    public function getEnPassantPosition()
    {
        return $this->enPassantPosition;
    }

    public function enPassantPositionExists(): bool
    {
        return isset($this->enPassantPosition);
    }
}