<?php

namespace AoC\Common;;

readonly class Point
{
    /**
     * Assume we're working with coordinates in a typical 2D array, with top left at (y=0, x=0)
     * @param int $y
     * @param int $x
     */
    public function __construct(public int $y, public int $x) {}

    public function getInDirection(Direction2D $direction): Point
    {
        return match ($direction) {
            Direction2D::Up => new Point($this->x, $this->y - 1),
            Direction2D::Down => new Point($this->x, $this->y + 1),
            Direction2D::Left => new Point($this->x - 1, $this->y),
            Direction2D::Right => new Point($this->x + 1, $this->y)
        };
    }

    public function equals(Point $point): bool
    {
        return $point->x === $this->x && $point->y === $this->y;
    }

    public function __toString(): string
    {
        return "({$this->x}, {$this->y})";
    }
}
