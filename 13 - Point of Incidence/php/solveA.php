<?php
declare(strict_types=1);

namespace AoC\Thirteen;

use AoC\Common\Logger;

require_once __DIR__ . '/common.php';
require_once __DIR__ . '/../../common/php/autoload.php';

$logger = new Logger();

$maps = getMirrorMaps();
$total = 0;
foreach ($maps as $index => $map) {
    $map->logger = $logger;
    $logger->log("Map $index:");
    $logger->log($map->getDiagram());

    $rows = $map->findReflectionRows();
    $logger->log("Found reflection rows:" . json_encode($rows));
    foreach ($rows as $row) {
        $total += 100 * $row;
    }

    $columns = $map->findReflectionColumns();
    $logger->log("Found reflection columns:" . json_encode($columns));
    foreach ($columns as $column) {
        $total += $column;
    }
}

echo $total . "\n";
