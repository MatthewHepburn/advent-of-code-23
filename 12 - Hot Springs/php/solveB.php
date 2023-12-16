<?php
declare(strict_types=1);

namespace AoC\Twelve;

use AoC\Common\Logger;

require_once __DIR__ . '/common.php';
require_once __DIR__ . '/../../common/php/autoload.php';

$logger = new Logger();

ini_set('memory_limit', '12G');

$total = 0;
foreach (getSpringRowsUnfolded() as $row) {
    $logger->log("Considering Row: " . $row);
    $count = $row->getOptionCount();
    $total += $count;
    $logger->log(    "Found $count valid options");
}

echo $total . "\n";
