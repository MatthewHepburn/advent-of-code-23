<?php
declare(strict_types=1);

namespace AoC\Twelve;

use AoC\Common\Logger;

require_once __DIR__ . '/common.php';
require_once __DIR__ . '/../../common/php/autoload.php';

$logger = new Logger();

$rows = getSpringRows();
$total = 0;
foreach ($rows as $row) {
    $logger->log("Considering Row: " . $row);
    $candidates = $row->getCandidates();
    $logger->log("  Identified " . count($candidates) . " potential candidates");
    $validCandidates = 0;
    foreach ($candidates as $candidate) {
        $valid = $candidate->isSatisfied();
        $statusString = $valid ? "Valid" : "Invalid";
        $logger->log("    $statusString - $candidate");
        if ($valid) {
            $validCandidates += 1;
        }
    }
    $total += $validCandidates;
    $logger->log(    "Found $validCandidates valid options");
}

echo $total . "\n";
