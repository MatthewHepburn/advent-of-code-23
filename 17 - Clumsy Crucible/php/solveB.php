<?php
declare(strict_types=1);

namespace AoC\Seventeen;

use AoC\Common\Logger;

require_once __DIR__ . '/common.php';
require_once __DIR__ . '/../../common/php/autoload.php';

ini_set('memory_limit', '12G');

$logger = new Logger();

$map = getMap(true);
$map->logger = $logger;
$map->searchUltraCrucible();
$logger->log($map->getBestCostDiagram());

echo $map->squares[count($map->squares) - 1][count($map->squares[0]) - 1]->costs->getBestCost() . "\n";

