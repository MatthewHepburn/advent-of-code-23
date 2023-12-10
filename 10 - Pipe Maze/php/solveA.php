<?php
declare(strict_types=1);

namespace AoC\Ten;

use AoC\Common\Logger;

require_once __DIR__ . '/common.php';
require_once __DIR__ . '/../../common/php/Logger.php';

$logger = new Logger();

$maze = getMaze();

$maze->print();

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
            $logger->log("Found " . count($connectedPipes) . " connections from $startPipe at {$startPipe->getPosition()}");
            foreach ($maze->getConnectedPipes($startPipe) as $connectedPipe) {
                $newFrontier[]= $connectedPipe;
            }
        }
    }
    $frontier = $newFrontier;
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

echo $furthestPipeSteps . "\n";
