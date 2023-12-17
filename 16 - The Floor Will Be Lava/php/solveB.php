<?php
declare(strict_types=1);

namespace AoC\Sixteen;

use AoC\Common\InputLoader;
use AoC\Common\Logger;

require_once __DIR__ . '/common.php';
require_once __DIR__ . '/../../common/php/autoload.php';

$logger = new Logger();

$input = (new InputLoader(__DIR__))->getAsCharArray();

$maxCount = 1;
for ($i = 0; $i < count($input); $i++) {
    // Try energising from the west:
    $logger->log("Computing row $i from the west");
    $map = new ContraptionMap($input);
    $map->energiseFromWest($i, 0);
    $map->run();
    $maxCount = max($maxCount, $map->getEnergisedCount());

    // Try energising from the east:
    $logger->log("Computing row $i from the east");
    $map = new ContraptionMap($input);
    $map->energiseFromEast($i, count($input[0]) - 1);
    $map->run();
    $maxCount = max($maxCount, $map->getEnergisedCount());
}

for ($j = 0; $j < count($input[0]); $j++) {
    // From the north:
    $map = new ContraptionMap($input);
    $map->energiseFromNorth(0, $j);
    $map->run();
    $maxCount = max($maxCount, $map->getEnergisedCount());

    // From the south:
    $map = new ContraptionMap($input);
    $map->energiseFromSouth(count($input) - 1, $j);
    $map->run();
    $maxCount = max($maxCount, $map->getEnergisedCount());
}

echo $maxCount . "\n";
