<?php

namespace AoC\Common\Search;

interface RouteInterface
{
    /**
     * @return VertexInterface[]
     */
    public function getVertices(): array;

    public function getTotalCost(): CostInterface;

    public function withVertex(VertexInterface $vertex): self;
}
