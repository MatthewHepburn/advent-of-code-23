<?php
declare(strict_types=1);

namespace AoC\Six;

use AoC\Common\InputLoader;
use AoC\Common\Logger;

require_once __DIR__ . '/common.php';
require_once __DIR__ . '/../../common/php/Logger.php';

$logger = new Logger();

/**
 * @return RaceRecord[];
 */
function getRaceRecords(): array {
    [$timeString, $distanceString] = (new InputLoader(__DIR__))->getAsStrings();
    $times = extractInts($timeString);
    $distances = extractInts($distanceString);

    $output = [];
    for ($i = 0; $i < count($times); $i++) {
        $output[] = new RaceRecord($times[$i], $distances[$i]);
    }
    return $output;
}

$raceRecord = getRaceRecords();
$winningApproachesByRace = [];
foreach ($raceRecord as $raceRecord) {
    $winningApproachesForRace = 0;
    $logger->log("T: {$raceRecord->duration} D: {$raceRecord->distance}");
    for ($holdTime = 1; $holdTime < $raceRecord->duration; $holdTime++) {
        $distance = distanceForHoldTime($holdTime, $raceRecord->duration);
        $logger->log("  Hold: $holdTime, distance = $distance " . ($distance > $raceRecord->distance ? "Win!" : ''));
        if ($distance > $raceRecord->distance) {
            $winningApproachesForRace += 1;
        }
    }
    $winningApproachesByRace[] = $winningApproachesForRace;
}

$logger->log("By race: " . json_encode($winningApproachesByRace));

echo array_product($winningApproachesByRace) . "\n";
