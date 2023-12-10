<?php

namespace AoC\Common\Search;

interface VertexInterface
{
    public function getStart(): NodeInterface;
    public function getEnd(): NodeInterface;

    public function getCost(): CostInterface;
}
