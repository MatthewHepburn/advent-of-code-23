<?php
declare(strict_types=1);

namespace AoC\Seventeen;

use AoC\Common\Logger;

require_once __DIR__ . '/common.php';
require_once __DIR__ . '/../../common/php/autoload.php';

$logger = new Logger();

$map = getMap();
$map->logger = $logger;
$map->searchUltraCrucible();
$logger->log($map->getBestCostDiagram());

$endSquare = $map->squares[count($map->squares) - 1][count($map->squares[0]) - 1];

echo $endSquare->costs->getBestUltraCrucibleCosts() . "\n";

