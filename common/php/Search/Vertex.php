<?php

namespace AoC\Common\Search;

readonly class Vertex implements VertexInterface
{
    public function __construct(
        private NodeInterface $start,
        private NodeInterface $end,
        private CostInterface $cost
    ) {}

    public function getStart(): NodeInterface
    {
        return $this->start;
    }

    public function getEnd(): NodeInterface
    {
        return $this->end;
    }

    public function getCost(): CostInterface
    {
        return $this->cost;
    }
}
