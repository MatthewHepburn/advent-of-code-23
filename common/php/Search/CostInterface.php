<?php

namespace AoC\Common\Search;

interface CostInterface
{
    public function add(CostInterface $cost): CostInterface;

    /**
     * Comparator function for sorting
     */
    public function compare(self $b): int;
}
