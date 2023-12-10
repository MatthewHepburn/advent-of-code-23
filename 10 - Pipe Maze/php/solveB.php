<?php
declare(strict_types=1);

namespace AoC\Ten;

use AoC\Common\Logger;
use AoC\Common\Point;

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

/** @var Ground[][] $ground */
$groundArray = [];
/** @var Ground[] $groundFrontier */
$groundFrontier = [];
for ($y = 0; $y < count($maze->pipes); $y++) {
    $groundRow = [];
    for ($x = 0; $x < count($maze->pipes[$y]); $x++) {
        $pipe = $maze->pipes[$y][$x];
        $isExit = $pipe->isGround() && $maze->isEdge($pipe->getPosition());
        $ground = new Ground($pipe->getPosition(), !$pipe->isGround(), $isExit);
        $groundRow []= $ground;
        if ($isExit) {
            $groundFrontier[]= $ground;
        }
    }

    $groundArray[]= $groundRow;
}

$groundMaze = new GroundMaze($groundArray);

$distance = 0;
$improved = true;
while ($improved) {
    $improved = false;
    $newFrontier = [];
    foreach ($groundFrontier as $startGround) {
        $logger->log("Considering ground at {$ground->getPosition()}");
        $wasImprovement = $startGround->groundData->recordNewDistance($distance);
        if ($wasImprovement) {
            $logger->log("  Improvement - got to ground at {$startGround->getPosition()} in $distance steps");
            $improved = true;
            // Add connected ground to the new frontier
            $connectedGrounds = $groundMaze->getConnectedGround($startGround);
            $logger->log("    Found " . count($connectedGrounds) . " connections from ground at {$startGround->getPosition()}");
            foreach ($connectedGrounds as $connectedGround) {
                $newFrontier[]= $connectedGround;
            }
        }
    }
    $groundFrontier = $newFrontier;
    $distance += 1;
}

$logger->log($groundMaze->getEnclosureDiagram());

$enclosedGroundCount = 0;
foreach ($groundMaze->ground as $groundRow) {
    foreach ($groundRow as $ground) {
        if ($ground->isEnclosed()) {
            $enclosedGroundCount += 1;
        }
    }
}

echo $enclosedGroundCount . "\n";
