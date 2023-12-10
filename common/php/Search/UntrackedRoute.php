<?php

namespace AoC\Common\Search;

/**
 * When determining the path taken is not necessary
 */
readonly class UntrackedRoute implements RouteInterface
{
    public function __construct(
        protected CostInterface $totalCost
    ) { }

    public function getVertices(): array
    {
        return [];
    }

    public function getTotalCost(): CostInterface
    {
        return $this->totalCost;
    }

    public function withVertex(VertexInterface $vertex): RouteInterface
    {
        return new self(
            $this->totalCost->add($vertex->getCost())
        );
    }
}
