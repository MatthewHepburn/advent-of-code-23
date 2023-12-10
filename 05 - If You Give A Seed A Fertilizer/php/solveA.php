<?php
declare(strict_types=1);

namespace AoC\Five;

use AoC\Common\Logger;

require_once __DIR__ . '/common.php';
require_once __DIR__ . '/../../common/php/autoload.php';

$logger = new Logger();

$seeds = getSimpleSeeds();
$logger->log("Seeds: " . json_encode($seeds));

$maps = getMaps();
foreach ($maps as $map) {
    $logger->log("Got map from $map->from to $map->to");
    foreach ($map->rangeMaps as $rangeMap) {
        $logger->log("    sourceStart: $rangeMap->sourceStart, destStart: $rangeMap->destStart, len = $rangeMap->length");
    }
}

$locations = [];
foreach ($seeds as $seed) {
    $value = $seed;
    foreach ($maps as $map) {
        $newValue = $map->mapsToForwards($value);
        $logger->log("Mapped $map->from #$value to $map->to #$newValue");
        $value = $newValue;
    }

    $logger->log("Ended with location $value");
    $locations[] = $value;
}

echo min($locations) . "\n";
