<?php
declare(strict_types=1);

namespace AoC\Ten;

use AoC\Common\Logger;
use AoC\Common\Search\Problem as SearchProblem;

require_once __DIR__ . '/common.php';
require_once __DIR__ . '/../../common/php/autoload.php';

$logger = new Logger();

$maze = getMaze();

$logger->log($maze->getPipeDiagram());

$problem = new SearchProblem(
    $maze,
    [$maze->startPipe]
);
$problem->search();


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
