<?php

namespace AoC\Common\Search;

final readonly class NullCost implements CostInterface
{
    public function add(CostInterface $cost): CostInterface
    {
        return $cost;
    }

    public function compare(CostInterface $b): int
    {
        return 1;
    }
}
