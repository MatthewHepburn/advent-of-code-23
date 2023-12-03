<?php
declare(strict_types=1);

namespace AoC\Three;

use AoC\Common\AdjacenyGenerator2D;
use AoC\Common\InputLoader;
use AoC\Common\Logger;

require_once __DIR__ . '/../../common/php/AdjacenyGenerator2D.php';
require_once __DIR__ . '/../../common/php/Logger.php';
require_once __DIR__ . '/../../common/php/InputLoader.php';
require_once __DIR__ . '/common.php';

$logger = new Logger();

$schematic = (new InputLoader(__DIR__))->getAsCharArray();

$partNumbersByStartPos = [];
$adjacencyGenerator = new AdjacenyGenerator2D(0, 0, count($schematic) - 1, count($schematic[0]) - 1, true);
for ($i = 0; $i < count($schematic); $i++) {
    for ($j = 0; $j < count($schematic[$i]); $j++) {
        $char = $schematic[$i][$j];
        if (preg_match('/[^.0-9]/', $char)) {
            // We have a symbol! Do we have any adjacent numbers
            $logger->log("Found Symbol: '$char' @ $i,$j");
            foreach ($adjacencyGenerator->getAdjacent($i, $j) as [$adjI, $adjJ]) {
                $maybeNumber = extractNumber($schematic, $adjacencyGenerator, $adjI, $adjJ);
                if ($maybeNumber) {
                    if (!isset($partNumbersByStartPos[$maybeNumber->position])) {
                        $logger->log("Found new part number: {$maybeNumber->partNumber} @ {$maybeNumber->position}");
                        $partNumbersByStartPos[$maybeNumber->position] = $maybeNumber->partNumber;
                    }
                }
            }
        }
    }
}

echo array_sum($partNumbersByStartPos) . "\n";
