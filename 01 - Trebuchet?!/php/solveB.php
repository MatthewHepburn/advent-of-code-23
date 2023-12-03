<?php

namespace AoC\One;

use AoC\Common\InputLoader;
use AoC\Common\Logger;

require_once __DIR__ . '/../../common/php/InputLoader.php';
require_once __DIR__ . '/../../common/php/Logger.php';

$logger = new Logger();

$lines = (new InputLoader(__DIR__))->getAsStrings();

$digitMapping = [
    'one' => '1',
    'two' => '2',
    'three' => '3',
    'four' => '4',
    'five' => '5',
    'six' => '6',
    'seven' => '7',
    'eight' => '8',
    'nine' => '9'
];

do {
    $changed = false;

    // Convert any words at the start of our strings:
    foreach ($digitMapping as $spelled => $digit) {
        for ($i = 0; $i < count($lines); $i++) {
            $line = $lines[$i];
            $newLine = preg_replace("/^$spelled/", $digit, $line);
            if ($line !== $newLine) {
                $changed = true;
                $logger->log("Changed $line to $newLine");
                $lines[$i] = $newLine;
            }
        }
    }

    // Convert any words at the end of our strings:
    foreach ($digitMapping as $spelled => $digit) {
        for ($i = 0; $i < count($lines); $i++) {
            $line = $lines[$i];
            $newLine = preg_replace("/$spelled$/", $digit, $line);
            if ($line !== $newLine) {
                $changed = true;
                $logger->log("Changed $line to $newLine");
                $lines[$i] = $newLine;
            }
        }
    }

    // Discard any leading or trailing non-digits
    for ($i = 0; $i < count($lines); $i++) {
        $line = $lines[$i];
        $newLine = preg_replace('/^[^0-9]/', '', $line);
        $newLine = preg_replace('/[^0-9]$/', '', $newLine);

        if ($line !== $newLine) {
            $changed = true;
            $logger->log("Changed $line to $newLine");
            $lines[$i] = $newLine;
        }
    }

} while ($changed);

$digitOnlyStrings = array_map(fn (string $x) => preg_replace('/[^0-9]*/', '', $x), $lines);

$ints = array_map(function (string $x) use ($logger) {
    $tens = (int) $x[0];
    $units = (int) $x[strlen($x) - 1];
    $output = (10 * $tens) + $units;
    $logger->log("x = $x, tens = $tens, units = $units, output = $output");
    return $output;
}, $digitOnlyStrings);


echo array_sum($ints) . "\n";
