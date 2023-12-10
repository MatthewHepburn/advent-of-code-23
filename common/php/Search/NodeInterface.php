<?php

namespace AoC\Common\Search;

interface NodeInterface
{
    public function getVertices(): array;

    public function isImprovement(RouteInterface $route): bool;

    public function getBestRoute(): ?RouteInterface;
}
