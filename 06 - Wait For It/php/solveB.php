<?php
declare(strict_types=1);

namespace AoC\Six;

use AoC\Common\InputLoader;
use AoC\Common\Logger;

require_once __DIR__ . '/common.php';
require_once __DIR__ . '/../../common/php/Logger.php';

$logger = new Logger();

/**
 * @return RaceRecord;
 */
function getRaceRecord(): RaceRecord {
    [$timeString, $distanceString] = (new InputLoader(__DIR__))->getAsStrings();
    $timeString = str_replace(' ', '', $timeString);
    $distanceString = str_replace(' ', '', $distanceString);

    [$discard, $time] = explode(':', $timeString);
    [$discard, $distance] = explode(':', $distanceString);

    return new RaceRecord((int) $time, (int) $distance);
}

$raceRecord = getRaceRecord();
$winningApproachesForRace = 0;
$logger->log("T: {$raceRecord->duration} D: {$raceRecord->distance}");

// Our win region is continuous, the equation governing the distance for a given hold time is quadratic.
// We just need to identify the lowest and highest hold times that win and go from there
$lowestWinner = null;
$highestWinner = null;
for ($holdTime = (int) floor($raceRecord->duration / 2); $holdTime < $raceRecord->duration; $holdTime++) {
    $distance = distanceForHoldTime($holdTime, $raceRecord->duration);
//    $logger->log("  Hold: $holdTime, distance = $distance " . ($distance > $raceRecord->distance ? "Win!" : ''));
    if ($distance > $raceRecord->distance) {
        $highestWinner = $holdTime;
    } else if ($highestWinner) {
        // If this isn't a winner and we have a highest winner, stop the search
        $logger->log("Found highest winning hold time: $highestWinner");
        break;
    }
}

for ($holdTime = (int) floor($raceRecord->duration / 2); $holdTime > 0; $holdTime--) {
    $distance = distanceForHoldTime($holdTime, $raceRecord->duration);
//    $logger->log("  Hold: $holdTime, distance = $distance " . ($distance > $raceRecord->distance ? "Win!" : ''));
    if ($distance > $raceRecord->distance) {
        $lowestWinner = $holdTime;
    } else if ($lowestWinner) {
        // If this isn't a winner and we have a highest winner, stop the search
        $logger->log("Found lowest winning hold time: $lowestWinner");
        break;
    }
}

$stratCount = $highestWinner - $lowestWinner + 1;

echo $stratCount . "\n";
