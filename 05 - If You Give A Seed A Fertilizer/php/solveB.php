<?php
declare(strict_types=1);

namespace AoC\Five;

use AoC\Common\Logger;

require_once __DIR__ . '/common.php';
require_once __DIR__ . '/../../common/php/Logger.php';

$logger = new Logger();;

$maps = getMaps();

// Work backwards
$maps = array_reverse($maps);

$seedRanges = getSeedRanges();

// Assume our output is going to come from one of the humidity-to-location mappings
// Can't prove that assumption is true, but it seems to work
$initialOutput = null;
foreach ($maps[0]->rangeMaps as $rangeMap) {
    $initialOutput = $initialOutput ? min($rangeMap->destStart, $initialOutput) : $rangeMap->destStart;
}

$logger->log("Initial output = $initialOutput");
$output = $initialOutput;
while (true) {
    $value = $output;
    foreach ($maps as $map) {
        $value = $map->mapsToBackwards($value);
    }

    if ($output % 1000 === 0) {
        $logger->log("Output $output maps to seed $value");
    }

    // Is that a valid seed?
    foreach ($seedRanges as $seedRange) {
        if ($seedRange->inRange($value)) {
            $logger->log("Found seed value $value");
            break 2;
        } else {
//            $logger->log("Value $value not in range $seedRange->start -> $seedRange->end");
        }
    }

    $output += 1;
}



echo $output . "\n";
