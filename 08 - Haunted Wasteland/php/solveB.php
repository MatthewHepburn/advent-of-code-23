<?php
declare(strict_types=1);

namespace AoC\Eight;

use AoC\Common\Logger;
use function AoC\Common\filter;

require_once __DIR__ . '/common.php';
require_once __DIR__ . '/../../common/php/autoload.php';

$logger = new Logger();

$problem = getProblem();

$positions = [];
foreach ($problem->nodesByName as $node) {
    if ($node->isANode) {
        $positions[]= $node;
    }
}

/**
 * @param Node[] $positions
 *
 * @return bool
 */
$atEnd = function (array $positions): bool {
    foreach ($positions as $node) {
        if (!$node->isZNode) {
            return false;
        }
    }

    return true;
};

$positionsToString = function (array $x) {
    $labels = array_map(fn(Node $x) => $x->name, $x);
    return implode(', ',$labels);
};

$steps = 0;


/**
 * All routes will settle into a repeating pattern, after some initial exploration phase
 * We want to know
 * 1 - how long it takes to get out of the initial exploration phase for all starting points
 * 2 - the length of each repeating route, and the positions on each route of the Z nodes
 *
 * Further, experiments suggest that each starting position leads to exactly one unique Z node and the exploratory period is effectively if not actually zero.
 * That is, after period P from the start we're at our Z node, after another period P, we're at our Z node again, NOT our start node.
 */
$periods = [];
foreach ($positions as $initialPosition) {
    $pos = $initialPosition;
    $seenZNodes = [];
    $zNodeVisits = [];
    $hasSeenAllZNodesThrice = false;
    $steps = 0;
    while (!$hasSeenAllZNodesThrice || count($seenZNodes) === 0) {
        $directionIndex = $steps % count($problem->directions);
        $direction = $problem->directions[$directionIndex];
        $steps += 1;

        $nextNodeName = $pos->getNodeNameInDirection($direction);
        $pos = $problem->nodesByName[$nextNodeName];
        if ($pos->isZNode) {
            $seenZNodes[$pos->name] ??= 0;
            $seenZNodes[$pos->name] += 1;
            $zNodeVisits[$pos->name] []= $steps;
        }

        $hasSeenAllZNodesThrice = count(filter($seenZNodes, fn(int $visits) => $visits > 2)) === count($seenZNodes);
    }

    if (count($zNodeVisits) !== 1) {
        throw new \Exception('Assumption violated - we visited more than one Z node on our journey');
    }

    [$firstVisitStep, $previousVisitStep, $lastVisitStep] = $zNodeVisits[$pos->name];
    $repeatPeriod = $lastVisitStep - $previousVisitStep;
    $exploratoryPeriod = $firstVisitStep - $repeatPeriod;

    $logger->log("Starting from {$initialPosition->name}, visited " . json_encode(array_keys($seenZNodes)) . " Z nodes after $steps steps. Repeat period = $repeatPeriod, exploratory period = $exploratoryPeriod");
    $periods[]= $repeatPeriod;
}

// Use our longest period to find our first moment of alignment
$longestPeriod = max($periods);
$step = 0;
while (true) {
    $step += $longestPeriod;
    foreach ($periods as $period) {
        if ($step % $period !== 0) {
            continue 2;
        }
    }
    break;
}

echo $step . "\n";
