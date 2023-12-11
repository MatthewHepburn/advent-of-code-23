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
            Direction2D::Up => new Point($this->y - 1, $this->x),
            Direction2D::Down => new Point($this->y + 1, $this->x),
            Direction2D::Left => new Point($this->y, $this->x - 1),
            Direction2D::Right => new Point($this->y, $this->x + 1)
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

    public function manhattanDistance(Point $destPoint): int
    {
        $xDist = abs($this->x - $destPoint->x);
        $yDist = abs($this->y - $destPoint->y);
        return $xDist + $yDist;
    }
}
