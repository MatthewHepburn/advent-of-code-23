<?php
declare(strict_types=1);

namespace AoC\Eighteen;

use AoC\Common\Logger;

require_once __DIR__ . '/common.php';
require_once __DIR__ . '/../../common/php/autoload.php';

$logger = new Logger();

$steps = getSteps();
$mapSpec = getMapSpec($steps);

$logger->log("MapSpec = $mapSpec");
$map = new ExcavationMap($mapSpec);
$map->followPlan($steps);
$logger->log("After:");
$logger->log($map->getDiagram());
