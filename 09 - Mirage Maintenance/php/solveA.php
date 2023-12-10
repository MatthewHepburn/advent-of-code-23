<?php
declare(strict_types=1);

namespace AoC\Nine;

use AoC\Common\Logger;

require_once __DIR__ . '/common.php';
require_once __DIR__ . '/../../common/php/autoload.php';

$logger = new Logger();

$sequences = getSequences();

$extrapolated = [];
foreach ($sequences as $sequence) {
    $logger->log("Starting sequence: " . json_encode($sequence));

    $lastSequence = $sequence;
    $derivativeSequences = [];
    do {
        $derivative = getDiffs($lastSequence);
        $logger->log("    " . json_encode($derivative));
        $derivativeSequences[]= $derivative;
        $lastSequence = $derivative;
    } while(!isZeros($derivative));

    $logger->log("    Found all zeros, working backwards to extrapolate");
    $newValue = 0;
    while ($derivative = array_pop($derivativeSequences)) {
        $lastValue = $derivative[count($derivative) - 1];
        $newValue = $lastValue + $newValue;
        $logger->log("        Adding new value $newValue");
    }

    // Finally, we get back to our original sequence
    $lastValue = $sequence[count($sequence) - 1];
    $extrapolatedValue = $lastValue + $newValue;
    $logger->log(    "Extrapolated $extrapolatedValue");
    $extrapolated[]= $extrapolatedValue;
}

echo array_sum($extrapolated) . "\n";
