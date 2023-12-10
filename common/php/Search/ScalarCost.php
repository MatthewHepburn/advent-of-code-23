<?php

namespace AoC\Common\Search;

readonly class ScalarCost implements CostInterface
{
    public function __construct(public int|float $value) {}

    public function add(CostInterface $cost): CostInterface
    {
        if (!$cost instanceof ScalarCost) {
            throw new \Exception('Cannot add non-scalar cost to a scalar cost');
        }

        return new ScalarCost($this->value + $cost->value);
    }

    public function compare(CostInterface $b): int
    {
        $bValue = $b instanceof ScalarCost ? $b->value : 1;
        return $this->value <=> $bValue;
    }
}
