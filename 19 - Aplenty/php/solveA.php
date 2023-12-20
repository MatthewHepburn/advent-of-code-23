<?php
declare(strict_types=1);

namespace AoC\Nineteen;

use AoC\Common\Logger;

require_once __DIR__ . '/common.php';
require_once __DIR__ . '/../../common/php/autoload.php';

$logger = new Logger();

$problem = getProblem();

$total = 0;
foreach ($problem->parts as $part) {
    $target = 'in';
    while ($target !== Terminal::Accept->value && $target !== Terminal::Reject->value) {
        $workflow = $problem->workflows[$target];
        $target = $workflow->evaluateFor($part);
    }
    if ($target === Terminal::Accept->value) {
        $partScore = $part->getScore();
        $total+= $partScore;
        $logger->log("Accepted part $part for score $partScore");
    }
}

echo $total . "\n";
