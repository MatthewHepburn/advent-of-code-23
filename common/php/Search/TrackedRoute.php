<?php

namespace AoC\Common\Search;

/**
 * When determining the path taken is necessary
 */
readonly class TrackedRoute implements RouteInterface
{
    public function __construct(
        protected array $vertices,
        protected CostInterface $totalCost
    ) { }

    public function getVertices(): array
    {
        return $this->vertices;
    }

    public function getTotalCost(): CostInterface
    {
        return $this->totalCost;
    }

    public function withVertex(VertexInterface $vertex): RouteInterface
    {
        return new self(
            array_merge($this->vertices, [$vertex]),
            $this->totalCost->add($vertex->getCost())
        );
    }
}
