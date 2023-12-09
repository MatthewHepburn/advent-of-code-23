<?php
declare(strict_types=1);

namespace AoC\Eight;

use AoC\Common\Logger;

require_once __DIR__ . '/common.php';
require_once __DIR__ . '/../../common/php/Logger.php';

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
while (!$atEnd($positions)) {
    $directionIndex = $steps % count($problem->directions);
    $direction = $problem->directions[$directionIndex];
    $steps += 1;

    $newPositions = [];
    $newPositionNames = [];
    foreach ($positions as $position) {
        $nextNodeName = $position->getNodeNameInDirection($direction);
        if (!isset($newPositionNames[$nextNodeName])) {
            $newPositionNames[$nextNodeName] = $nextNodeName;
            $newPositions[] = $problem->nodesByName[$nextNodeName];
        } else {
            $logger->log("Discarding duplicate new position " . $nextNodeName);
        }
    }

    if ($steps % 100000 == 0)
    $logger->log("$steps Moving from " . $positionsToString($positions) . " to " . $positionsToString($newPositions));

    $positions = $newPositions;
}

echo $steps . "\n";
