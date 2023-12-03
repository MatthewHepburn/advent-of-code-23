<?php
declare(strict_types=1);

namespace AoC\Three;

use Aoc\Common\AdjacenyGenerator2D;
use InputLoader;
use Logger;

require_once __DIR__ . '/../../common/php/AdjacenyGenerator2D.php';
require_once __DIR__ . '/../../common/php/Logger.php';
require_once __DIR__ . '/common.php';

function extractNumber(array $schematic, AdjacenyGenerator2D $adjacenyGenerator, int $i, int $j): ?int
{
    $thisChar = $schematic[$i][$j];
    if (!ctype_digit($thisChar)) {
        return null;
    }

    // Move to the leftmost digit in our number
    while (true) {
        $nextPos = $adjacenyGenerator->getLeft($i, $j);
        if (!$nextPos) {
            break;
        }
        $nextChar = $schematic[$nextPos[0]][$nextPos[1]];
        if (!ctype_digit($nextChar)) {
            break;
        }
        [$i, $j] = $nextPos;
    }

    // Now read our digits left to right
    $digits = [];
    while (true) {
        $char = $schematic[$i][$j];
        if (!ctype_digit($char)) {
            break;
        }
        $digits[]= $char;
        $nextPos = $adjacenyGenerator->getRight($i, $j);
        if (!$nextPos) {
            break;
        }
        [$i, $j] = $nextPos;
    }

    $digitString = implode('', $digits);
    return (int) $digitString;
}

$logger = new Logger();

$schematic = (new InputLoader(__DIR__))->getAsCharArray();

$numbersSeen = [];
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
                    if (!isset($numbersSeen[$maybeNumber])) {
                        $logger->log("Found new part number: $maybeNumber");
                        $numbersSeen[$maybeNumber] = true;
                    }
                }
            }
        }
    }
}

echo array_sum(array_keys($numbersSeen)) . "\n";
