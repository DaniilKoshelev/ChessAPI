<?php

namespace App\Game;

use App\Exceptions\Game\InternalException;
use App\Exceptions\Game\InvalidMoveException;
use App\Http\JsonResponse;
use App\Http\JsonResponseFailure;
use App\Http\JsonResponseSuccess;
use App\Util\GameFileOperator;
use App\Util\Position;
use Exception;
use JsonSerializable;

class Game implements JsonSerializable
{
    private GameFileOperator $fileOperator;
    private Chessboard $chessboard;
    private string $currentPlayerColor;
    private string $status;

    public function __construct()
    {
        $this->chessboard = new Chessboard();
        $this->fileOperator = new GameFileOperator($this->chessboard);
    }

    /**
     * /move API endpoint. Moves piece from one position to another
     * @return JsonResponse
     */
    public function move(): JsonResponse
    {
        $this->load(config('FILE_GAME'));

        $oldStatus = $this->status;

        if ($oldStatus === STATUS_MATE)
            return new JsonResponseFailure('Game is already finished');

        try {
            request()->tryValidate(config('FILE_SCHEMA_MOVE'));

            $from = Position::tryParse(request('from'));
            $to = Position::tryParse(request('to'));

            $this->chessboard->tryMovePiece($from, $to);

            // Check if player's move removes check
            if ($oldStatus === STATUS_CHECK) {
                $newStatus = $this->chessboard->getStatus($this->currentPlayerColor);

                if ($newStatus !== STATUS_PLAYING)
                    return new JsonResponseFailure(
                        'Invalid move: player [' . $this->currentPlayerColor . '] needs to avoid check'
                    );
            }

            $this->save();

        } catch (InternalException $e) {
            return new JsonResponseFailure('Internal error');
        } catch (Exception $e) {
            return new JsonResponseFailure($e->getMessage());
        }

        return new JsonResponseSuccess();
    }

    /**
     * Load game from file
     * @param string $filename
     */
    private function load(string $filename): void
    {
        $this->fileOperator->parse($filename);

        $this->currentPlayerColor = $this->fileOperator->getCurrentPlayerColor();
        $this->status = $this->fileOperator->getStatus();

        $this->chessboard->setCurrentPlayerColor($this->currentPlayerColor);

        if ($this->fileOperator->enPassantPositionExists())
            $this->chessboard->setEnPassantPosition($this->fileOperator->getEnPassantPosition());
    }

    /**
     * Save current game state to file
     * @throws InternalException
     * @throws InvalidMoveException
     */
    private function save(): void
    {
        $this->status = $this->chessboard->getStatus(oppositeColor($this->currentPlayerColor));
        $this->currentPlayerColor = oppositeColor($this->currentPlayerColor);

        $this->fileOperator->save($this);
    }

    /**
     * /status API endpoint
     * @return JsonResponse
     */
    public function status(): JsonResponse
    {
        $this->load(config('FILE_GAME'));
        return new JsonResponseSuccess([
            'status' => $this->status
        ]);
    }

    /**
     * /start API endpoint. Start new game
     * @return JsonResponse
     */
    public function start(): JsonResponse
    {
        $this->load(config('FILE_NEW_GAME'));
        $this->fileOperator->save($this);

        return new JsonResponseSuccess();
    }

    public function getChessboard(): Chessboard
    {
        return $this->chessboard;
    }

    public function jsonSerialize()
    {
        return [
            'currentPlayerColor' => $this->currentPlayerColor,
            'status' => $this->status,
            'chessboard' => $this->chessboard
        ];
    }
}