<?php
declare(strict_types=1);

namespace AoC\Eighteen;

use AoC\Common\Logger;

require_once __DIR__ . '/common.php';
require_once __DIR__ . '/../../common/php/autoload.php';

$logger = new Logger();

$steps = getSteps();
$correctedSteps = array_map(fn(PlanStep $s) => $s->getCorrected(), $steps);
foreach ($correctedSteps as $correctedStep) {
    $logger->log((string) $correctedStep);
}
$mapSpec = getMapSpec($correctedSteps);

$logger->log("MapSpec = $mapSpec");
$map = new ExcavationMap($mapSpec, false);
$map->logger = $logger;
$map->followPlan($correctedSteps);

$total = $map->markInner();

echo $total . "\n";
