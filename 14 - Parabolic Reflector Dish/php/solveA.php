<?php
declare(strict_types=1);

namespace AoC\Fourteen;

use AoC\Common\Logger;

require_once __DIR__ . '/common.php';
require_once __DIR__ . '/../../common/php/autoload.php';

$logger = new Logger();

$map = getDishMap();
$logger->log("Before:");
$logger->log($map->getDiagram());
$map->tiltNorth();
$logger->log("After:");
$logger->log($map->getDiagram());

echo $map->getNorthLoad() . "\n";
