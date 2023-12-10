<?php
declare(strict_types=1);

namespace AoC\Three;

use AoC\Common\AdjacenyGenerator2D;
use AoC\Common\InputLoader;
use AoC\Common\Logger;

require_once __DIR__ . '/../../common/php/autoload.php';
require_once __DIR__ . '/common.php';

$logger = new Logger();

$schematic = (new InputLoader(__DIR__))->getAsCharArray();

$gearRatios = [];
$adjacencyGenerator = new AdjacenyGenerator2D(0, 0, count($schematic) - 1, count($schematic[0]) - 1, true);
for ($i = 0; $i < count($schematic); $i++) {
    for ($j = 0; $j < count($schematic[$i]); $j++) {
        $char = $schematic[$i][$j];
        if ($char === '*') {
            $logger->log("Found possible gear: '$char' @ $i,$j");
            $adjacentParts = [];
            foreach ($adjacencyGenerator->getAdjacent($i, $j) as [$adjI, $adjJ]) {
                $maybeNumber = extractNumber($schematic, $adjacencyGenerator, $adjI, $adjJ);
                if ($maybeNumber) {
                    if (!isset($adjacentParts[$maybeNumber->position])) {
                        $logger->log("Found new part number: {$maybeNumber->partNumber} @ {$maybeNumber->position}");
                        $adjacentParts[$maybeNumber->position] = $maybeNumber->partNumber;
                    }
                }
            }
            if (count($adjacentParts) === 2) {
                $logger->log("Identified gear @$i, $j");
                $gearRatios[] = array_product($adjacentParts);
            } else {
                $logger->log("Not a gear @$i, $j");
            }
        }
    }
}

echo array_sum($gearRatios) . "\n";
