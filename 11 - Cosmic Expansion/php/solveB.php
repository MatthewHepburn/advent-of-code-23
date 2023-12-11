<?php
declare(strict_types=1);

namespace AoC\Eleven;

use AoC\Common\Logger;

require_once __DIR__ . '/common.php';
require_once __DIR__ . '/../../common/php/autoload.php';

$logger = new Logger();

$map = getStarMap();

$galaxies = $map->getGalaxies();
$logger->log("Got Galaxies:");

$pairCount = 0;
$distanceSum = 0;
$emptyRows = $map->getEmptyRows();
$emptyColumns = $map->getEmptyColumns();

$expansionConstant = 1000000;

foreach ($galaxies as $number => $point) {
    $logger->log("    $number: $point");
    foreach ($galaxies as $destNumber => $destPoint) {
        if ($destNumber <= $number) {
            // Only consider pairs going from low to high galaxy numbers
            continue;
        }
        $pairCount += 1;

        $rowPoints = [$point->y, $destPoint->y];
        $manhattanDistance = 0;
        sort($rowPoints);
        for ($row = $rowPoints[0]; $row < $rowPoints[1]; $row++) {
            $distance = isset($emptyRows[$row]) ? $expansionConstant : 1;
            $manhattanDistance += $distance;
        }

        $columnPoints = [$point->x, $destPoint->x];
        sort($columnPoints);
        for ($column = $columnPoints[0]; $column < $columnPoints[1]; $column++) {
            $distance = isset($emptyColumns[$column]) ? $expansionConstant : 1;
            $manhattanDistance += $distance;
        }
        $logger->log("Distance from $number to $destNumber: $manhattanDistance");
        $distanceSum += $manhattanDistance;
    }
}

echo $distanceSum . "\n";
