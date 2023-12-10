<?php

namespace AoC\Common\Search;

class Problem
{
    private bool $trackRoute = false;
    private CostInterface $nullCost;

    /**
     * @param MapInterface $map
     * @param NodeInterface[] $initialNodes
     */
    public function __construct(
        public MapInterface $map,
        public array $initialNodes
    )
    {
        $this->nullCost = new NullCost();
    }

    public function search(): void {
        $nodes = $this->initialNodes;
        $improved = true;
        while ($improved) {
            $nextNodes = [];
            $improved = false;
            $frontier = [];
            foreach ($nodes as $node) {
                $frontier = array_merge($frontier, $this->map->getVerticesFrom($node));
            }
            echo "Frontier size = " . count($frontier) . "\n";
            $frontier = $this->sortFrontier($frontier);
            foreach ($frontier as $vertex) {
                $start = $vertex->getStart();
                $routeStart = $start->getBestRoute() ?? $this->newRouteFrom($start);
                $route = $routeStart->withVertex($vertex);
                $isImprovement = $vertex->getEnd()->isImprovement($route);
                if ($isImprovement) {
                    $improved = true;
                    $nextNodes[]= $vertex->getEnd();
                }
            }

            $nodes = $nextNodes;
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
        if ($this->trackRoute) {
            return new TrackedRoute([], $this->nullCost);
        }
        return new UntrackedRoute($this->nullCost);
    }
}
