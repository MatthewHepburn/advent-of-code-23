<?php

namespace AoC\Two;

use AoC\Common\Logger;

require_once __DIR__ . '/../../common/php/autoload.php';
require_once __DIR__ . '/common.php';

$logger = new Logger();

$allowedRedCubes = 12;
$allowedGreenCubes = 13;
$allowedBlueCubes = 14;

/** @var Game[] $games */
$games = getInput();
$validGames = [];
foreach ($games as $game) {
    $logger->log($game->toString());
    $validGame = true;

    foreach ($game->rounds as $round) {
        $redSeen = 0;
        $greenSeen = 0;
        $blueSeen = 0;
        foreach ($round as $action) {
            match ($action->colour) {
                Colour::RED => $redSeen += $action->count,
                Colour::BLUE => $blueSeen += $action->count,
                Colour::GREEN => $greenSeen += $action->count,
            };
        }
        if ($redSeen > $allowedRedCubes || $blueSeen > $allowedBlueCubes || $greenSeen > $allowedGreenCubes) {
            $validGame = false;
            $logger->log("Invalid game. Saw: r:$redSeen, b:$blueSeen, g:$greenSeen");
        }
    }

    if ($validGame) {
        $validGames[] = $game;
        $logger->log('Valid game');
    }
}

echo array_sum(array_map(fn(Game $g) => $g->id, $validGames)) . "\n";
