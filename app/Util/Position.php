<?php

namespace App\Util;

use JsonSerializable;
use ParseError;

class Position implements JsonSerializable
{
    public int $x;
    public int $y;

    public function __construct(int $x, int $y)
    {
        $this->x = $x;
        $this->y = $y;
    }

    /**
     * Get Position instance from the given string
     * @param string $position
     * @return Position
     * @throws ParseError
     */
    public static function tryParse(string $position): Position
    {
        $regExp = "/^([a-h]{1})([1-8]{1})$/";
        preg_match($regExp, $position, $matches);

        if (count($matches) !== 3)
            throw new ParseError('Wrong position format');

        $x = ord($matches[1]) - 97;
        $y = $matches[2] - 1;

        return new Position($x, $y);
    }

    // Shift current position by (x, y)
    public function move(int $x, int $y): void
    {
        $this->x += $x;
        $this->y += $y;
    }

    // Get new position with (x, y) shift
    public function add(int $x, int $y): Position
    {
        return new Position($this->x + $x, $this->y + $y);
    }

    public function jsonSerialize()
    {
        $x = chr($this->x + 97);
        $y = $this->y + 1;

        return $x . $y;
    }

    public function __toString(): string
    {
        $x = chr(97 + $this->x);
        $y = $this->y + 1;

        return "($x; $y)";
    }

    public function isValid(): bool
    {
        return ($this->x >= 0 && $this->x <= 7 && $this->y >= 0 && $this->y <= 7);
    }
}