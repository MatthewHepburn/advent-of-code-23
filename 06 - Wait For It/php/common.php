<?php
declare(strict_types=1);

namespace AoC\Six;

use AoC\Common\InputLoader;

require_once __DIR__ . '/../../common/php/InputLoader.php';

final readonly class RaceRecord {
    public function __construct(
        public int $duration,
        public int $distance
    ) {}
}

function distanceForHoldTime(int $holdTime, int $timeLimit): int {
    if ($holdTime === 0 || $holdTime >= $timeLimit) {
        return 0;
    }

    $speed = $holdTime;
    $remainingTime = $timeLimit - $holdTime;
    $distance = $remainingTime * $speed;

    return $distance;
}

function extractInts(string $str): array {
    [$label, $data] = preg_split('/: +/', $str);
    $intStrings = preg_split('/ +/', $data);
    $output = [];
    foreach ($intStrings as $intString) {
        $output[]= (int) $intString;
    }
    return $output;
}

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