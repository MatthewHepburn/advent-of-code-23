<?php

require_once __DIR__ . '/../../common/php/Logger.php';
require_once __DIR__ . '/common.php';

$logger = new Logger();

/** @var Game[] $games */
$games = getInput();
$powerSum = 0;
foreach ($games as $game) {
    $logger->log($game->toString());
    $minRed = 0;
    $minGreen = 0;
    $minBlue = 0;

    foreach ($game->rounds as $round) {
        foreach ($round as $action) {
            match ($action->colour) {
                Colour::RED => $minRed = max($minRed, $action->count),
                Colour::BLUE => $minBlue = max($minBlue, $action->count),
                Colour::GREEN => $minGreen = max($minGreen, $action->count),
            };
        }
    }
    $power = $minRed * $minGreen * $minBlue;
    $logger->log("Game could be played with r:$minRed, g:$minGreen, b:$minBlue giving power = $power");
    $powerSum += $power;
}

echo $powerSum . "\n";
