<?php
declare(strict_types=1);

namespace AoC\Sixteen;

use AoC\Common\Logger;

require_once __DIR__ . '/common.php';
require_once __DIR__ . '/../../common/php/autoload.php';

$logger = new Logger();

$map = getContraptionMap();
$logger->log("Before:");
$logger->log($map->getDiagram());
$map->run();
$logger->log("After (beams):");
$logger->log($map->getDiagram());
$logger->log("After (energy):");
$logger->log($map->getEnergyDiagram());

echo $map->getEnergisedCount() . "\n";
