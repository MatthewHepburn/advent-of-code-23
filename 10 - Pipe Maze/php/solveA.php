<?php
declare(strict_types=1);

namespace AoC\Ten;

use AoC\Common\Logger;

require_once __DIR__ . '/common.php';
require_once __DIR__ . '/../../common/php/Logger.php';

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
        $logger->log("Considering $startPipe at {$startPipe->getPosition()}");
        $wasImprovement = $startPipe->pipeData->recordNewDistance($distance);
        if ($wasImprovement) {
            $logger->log("  Improvement - got to $startPipe at {$startPipe->getPosition()} in $distance steps");
            $improved = true;
            // Add connected pipes to the new frontier
            $connectedPipes = $maze->getConnectedPipes($startPipe, $logger);
            $logger->log("    Found " . count($connectedPipes) . " connections from $startPipe at {$startPipe->getPosition()}: " . implode(' ', $connectedPipes));
            foreach ($connectedPipes as $connectedPipe) {
                $newFrontier[]= $connectedPipe;
            }
        }
    }
    $frontier = $newFrontier;
    $distance += 1;
}

$furthestPipeSteps = 0;
for ($y = 0; $y < count($maze->pipes); $y++) {
    for ($x = 0; $x < count($maze->pipes[$y]); $x++) {
        $pipe = $maze->pipes[$y][$x];
        if ($pipe->pipeData->minDistanceFromStart === null) {
            continue;
        }
        $furthestPipeSteps = max($furthestPipeSteps, $pipe->pipeData->minDistanceFromStart);
    }
}

$logger->log($maze->getDistanceDiagram());

echo $furthestPipeSteps . "\n";
