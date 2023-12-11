<?php
declare(strict_types=1);

namespace AoC\Eleven;

use AoC\Common\Logger;

require_once __DIR__ . '/common.php';
require_once __DIR__ . '/../../common/php/autoload.php';

$logger = new Logger();

$map = getStarMap();

$logger->log("Before Expansion:");
$logger->log($map->getStarDiagram());

$map->expand();

$logger->log("After Expansion:");
$logger->log($map->getStarDiagram());

$galaxies = $map->getGalaxies();
$logger->log("Got Galaxies:");

$pairCount = 0;
$distanceSum = 0;
foreach ($galaxies as $number => $point) {
    $logger->log("    $number: $point");
    foreach ($galaxies as $destNumber => $destPoint) {
        if ($destNumber <= $number) {
            // Only consider pairs going from low to high galaxy numbers
            continue;
        }
        $pairCount += 1;
        $distanceSum += $point->manhattanDistance($destPoint);
    }
}

$logger->log("We have $pairCount pairs of galaxies");
echo $distanceSum . "\n";
