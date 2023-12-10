<?php

namespace AoC\Common\Search;

class Problem
{
    /**
     * @param NodeInterface[] $initialNodes
     * @param NodeInterface[] $targetNodes
     */
    public function __construct(
        public array $initialNodes,
        public array $targetNodes
    )
    { }

    public function search(): void {
        $nextNodes = $this->initialNodes;
        $improved = true;
        while ($improved) {
            $improved = false;
            $frontier = [];
            foreach ($nextNodes as $node) {
                $frontier = array_merge($frontier, $node->getVertices());
            }
            $frontier = $this->sortFrontier($frontier);
            foreach ($frontier as $vertex) {
                $start = $vertex->getStart();
                $routeStart = $start->getBestRoute() ?? $this->newRouteFrom($start);
                $route = $routeStart->withVertex($vertex);
                $isImprovement = $vertex->getEnd()->isImprovement($route);
                $improved = $isImprovement ? true : $improved;
            }
        }
    }

    /**
     * @param VertexInterface[] $frontier
     *
     * @return VertexInterface[]
     */
    protected function sortFrontier(array $frontier): array
    {
        usort($frontier, fn(VertexInterface $a, VertexInterface $b) => $a->getCost()->compare($b->getCost()));
        return $frontier;
    }

    protected function newRouteFrom(NodeInterface $start): RouteInterface
    {
        return new Route([], new NullCost());
    }
}
