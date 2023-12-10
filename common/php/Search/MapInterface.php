<?php

namespace AoC\Common\Search;


interface MapInterface
{
   public function getVerticesFrom(NodeInterface $node): array;
}
