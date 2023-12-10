<?php
declare(strict_types=1);

namespace AoC\Ten;

use AoC\Common\Logger;

require_once __DIR__ . '/common.php';
require_once __DIR__ . '/../../common/php/autoload.php';

$logger = new Logger();

$maze = getMaze();

$logger->log($maze->getPipeDiagram());

/** @var Pipe[] $frontier */
$frontier = [$maze->startPipe];
$distance = 0;
$improved = true;
while ($improved) {
    $improved = false;
    $newFrontier = [];
    foreach ($frontier as $startPipe) {
        $wasImprovement = $startPipe->pipeData->recordNewDistance($distance);
        if ($wasImprovement) {
            $improved = true;
            // Add connected pipes to the new frontier
            $connectedPipes = $maze->getConnectedPipes($startPipe);
            foreach ($connectedPipes as $connectedPipe) {
                $newFrontier[]= $connectedPipe;
            }
        }
    }
    $frontier = $newFrontier;
    $distance += 1;
}

for ($y = 0; $y < count($maze->pipes); $y++) {
    // Look left to right
    $lastPipeString = '';
    $insideLoop = false;
    for ($x = 0; $x < count($maze->pipes[0]); $x++) {
        $pipe = $maze->pipes[$y][$x];

        $partOfLoop = $pipe->isPartOfLoop();
        if ($pipe->symbol === '|' && $partOfLoop) {
            $insideLoop = !$insideLoop;
            $lastPipeString = '';
        } elseif ($partOfLoop) {
            $lastPipeString .= $pipe->symbol;
        }

        if ($partOfLoop && strlen($lastPipeString) > 1 && $pipe->symbol !== '-' ) {
            // We might have crossed a boundary, depending on the sequence of pipes we've seen
            $logger->log("Considering pipe string: '$lastPipeString'");
            $firstPipe = $lastPipeString[0];
            $lastPipe = $lastPipeString[strlen($lastPipeString) - 1];
            if ($firstPipe === 'F' && ($lastPipe === 'J' || $lastPipe === 'S')) { // Hardcoded knowledge that our S is a J, should be deducible programmatically
                $logger->log("    Crossed a vertical boundary: $firstPipe $lastPipe");
                $insideLoop = !$insideLoop;
                $lastPipeString = '';
            } elseif ($firstPipe === 'L' && $lastPipe === "7") {
                $logger->log("    Crossed a vertical boundary: $firstPipe $lastPipe");
                $insideLoop = !$insideLoop;
                $lastPipeString = '';
            } else {
                $logger->log("    Didn't cross a vertical boundary: $firstPipe $lastPipe");
                $lastPipeString = '';
            }
        }

        if ($partOfLoop) {
            $pipe->pipeData->enclosed = false;
        } else {
            $pipe->pipeData->enclosed = $insideLoop;
        }
    }
}

$enclosedGroundCount = 0;
for ($y = 0; $y < count($maze->pipes); $y++) {
    for ($x = 0; $x < count($maze->pipes[$y]); $x++) {
        $pipe = $maze->pipes[$y][$x];
        if ($pipe->pipeData->enclosed) {
            $enclosedGroundCount += 1;
        }
    }
}

$logger->log($maze->getEnclosureDiagram());

echo $enclosedGroundCount . "\n";
