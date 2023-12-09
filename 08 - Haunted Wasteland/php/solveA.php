<?php
declare(strict_types=1);

namespace AoC\Eight;

use AoC\Common\Logger;

require_once __DIR__ . '/common.php';
require_once __DIR__ . '/../../common/php/Logger.php';

$logger = new Logger();

$problem = getProblem();

$pos = $problem->nodesByName['AAA'];
$target = 'ZZZ';
$steps = 0;
while ($pos->name !== $target) {
    $directionIndex = $steps % count($problem->directions);
    $direction = $problem->directions[$directionIndex];
    $nextNodeName = $pos->getNodeNameInDirection($direction);
    $logger->log("Moving {$direction->name} from {$pos->name} to $nextNodeName");
    $pos = $problem->nodesByName[$nextNodeName];
    $steps += 1;
}

echo $steps . "\n";
