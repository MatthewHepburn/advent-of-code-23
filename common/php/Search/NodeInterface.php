<?php

namespace AoC\Common\Search;

interface NodeInterface
{
    public function isImprovement(RouteInterface $route): bool;

    public function getBestRoute(): ?RouteInterface;
}
