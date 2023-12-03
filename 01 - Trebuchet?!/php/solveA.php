<?php

require_once __DIR__ . '/../../common/php/InputLoader.php';
require_once __DIR__ . '/../../common/php/Logger.php';

$logger = new Logger();

$lines = (new InputLoader(__DIR__))->getAsStrings();

$digitOnlyStrings = array_map(fn (string $x) => preg_replace('/[^0-9]*/', '', $x), $lines);

$ints = array_map(function (string $x) use ($logger) {
    $tens = (int) $x[0];
    $units = (int) $x[strlen($x) - 1];
    $output = (10 * $tens) + $units;
    $logger->log("x = $x, tens = $tens, units = $units, output = $output");
    return $output;
}, $digitOnlyStrings);


echo array_sum($ints) . "\n";
