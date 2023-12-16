<?php
declare(strict_types=1);

namespace AoC\Fifteen;

use AoC\Common\Logger;

require_once __DIR__ . '/common.php';
require_once __DIR__ . '/../../common/php/autoload.php';

$logger = new Logger();

$total = 0;
$input = getInput();
foreach (explode(',', $input) as $chunk) {
    $hash = HASHer::hashString($chunk);
    $total += $hash;
    $logger->log("Hashing of '$chunk' = $hash");
}


echo $total . "\n";
