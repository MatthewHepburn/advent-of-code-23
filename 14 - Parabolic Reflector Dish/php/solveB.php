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

$signaturesToIterations = [];
$iterations = 1000000000;
$haveFastForwarded = false;
for ($i = 0; $i < $iterations; $i++) {
    $logger->log("Cycle " . $i + 1);
    $map->cycle();
    $signature = $map->getSignature();
    if (!$haveFastForwarded && isset($signaturesToIterations[$signature])) {
        $lastSeenAt = $signaturesToIterations[$signature];
        $iterationsToLoop = $i - $lastSeenAt;
        $logger->log("Found repeated signature. \$i = $i, \$lastSeenAt = $lastSeenAt, \$iterationsToLoop = $iterationsToLoop");
        $remainingIterations = $iterations - $i;
        $loopsToSkip = intdiv($remainingIterations, $iterationsToLoop);
        $logger->log("Fast forwarding by $loopsToSkip loops of $iterationsToLoop iterations");
        $i += $loopsToSkip * $iterationsToLoop;
        $haveFastForwarded = true;
    } else {
        $signaturesToIterations[$signature] = $i;
    }
}
$logger->log("After:");
$logger->log($map->getDiagram());

echo $map->getNorthLoad() . "\n";
