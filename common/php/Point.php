<?php

namespace Common;

class Point
{
    public function __construct(private int $x, private int $y) {}

    public function getX(): int
    {
        return $this->x;
    }

    public function getY(): int
    {
        return $this->y;
    }

    public function __toString(): string
    {
        return "({$this->x}, {$this->y})";
    }
}
