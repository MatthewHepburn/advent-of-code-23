<?php
declare(strict_types=1);

namespace AoC\Fifteen;

use AoC\Common\Logger;

require_once __DIR__ . '/common.php';
require_once __DIR__ . '/../../common/php/autoload.php';

$logger = new Logger();

/** @var Box[] $boxes */
$boxes = [];
for ($i = 0; $i < 256; $i++) {
    $boxes[$i] = new Box($i);
}

$input = getInput();
foreach (explode(',', $input) as $instruction) {
    if (str_contains($instruction, '=')) {
        [$label, $focalLength] = explode('=', $instruction);
        $boxNumber = HASHer::hashString($label);
        $newLens = new Lens($label, (int) $focalLength);
        $boxes[$boxNumber]->addLens($newLens);
    } else {
        [$label] = explode('-', $instruction);
        $boxNumber = HASHer::hashString($label);
        $boxes[$boxNumber]->removeLens($label);
    }

    $logger->log("After $instruction:");
    foreach ($boxes as $box) {
        if (!$box->hasLenses()) {
            continue;
        }
        $logger->log((string) $box);
    }
}

$logger->log("-----------------");
$total = 0;
foreach ($boxes as $box) {
    if (!$box->hasLenses()) {
        continue;
    }
    $power = $box->getPower();
    $logger->log("Box $box: $power");
    $total += $power;
}

echo $total . "\n";
