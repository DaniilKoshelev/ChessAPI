<?php

namespace App\Game\Pieces;

use App\Exceptions\Game\InvalidMoveException;
use App\Util\Position;

abstract class AbstractBlockable extends Piece
{
    /**
     * @param Position $position
     * @throws InvalidMoveException
     */
    public function tryMoveToPosition(Position $position): void
    {
        if ($this->checkDefaultMove($position) && !$this->foundBlockingPieces($position)) {
            parent::tryMoveToPosition($position);
            return;
        }

        throw new InvalidMoveException(
            'Invalid move for piece [' . static::getName() . '] on position ' . $this->getPosition()
        );
    }

    public abstract function checkDefaultMove(Position $position): bool;

    /**
     * @param Position $stop
     * @return bool
     * @throws InvalidMoveException
     */
    public function foundBlockingPieces(Position $stop): bool
    {
        return $this->findBlockingPieces($stop) !== [];
    }

    /**
     * find blocking pieces on the way to position
     * @param Position $stop
     * @param bool $stopAfterFirstFound
     * @return array
     * @throws InvalidMoveException
     */
    public function findBlockingPieces(Position $stop, bool $stopAfterFirstFound = true): array
    {
        $xStep = direction($stop->x, $this->x());
        $yStep = direction($stop->y, $this->y());
        $blockingPieces = [];

        $position = $this->getPosition()->add($xStep, $yStep);

        while ($position != $stop) {
            if (!$this->positionIsFree($position)) {

                $blockingPieces[] = $this->getChessboard()->getCell($position)->getPiece();

                if ($stopAfterFirstFound)
                    break;
            }
            $position->move($xStep, $yStep);
        }

        return $blockingPieces;
    }

    /**
     * @param Position $position
     * @return bool
     * @throws InvalidMoveException
     */
    public function canCapturePosition(Position $position): bool
    {
        return parent::canCapturePosition($position) && !$this->foundBlockingPieces($position);
    }
}