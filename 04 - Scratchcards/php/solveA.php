<?php
declare(strict_types=1);

namespace AoC\Four;

use AoC\Common\Logger;

require_once __DIR__ . '/common.php';
require_once __DIR__ . '/../../common/php/Logger.php';

$logger = new Logger();

$totalScore = 0;
foreach (getGames() as $game) {
    $logger->log("Card: {$game->gameNumber}: your numbers = " . json_encode($game->yourNumbers) . ", winning = " . json_encode($game->winningNumbers));
    $matches = 0;
    foreach ($game->winningNumbers as $winningNumber) {
        if (in_array($winningNumber, $game->yourNumbers)) {
            $matches += 1;
        }
    }
    $gameScore = $matches ? pow(2, $matches - 1) : 0;
    $totalScore += $gameScore;
    $logger->log("    Card {$game->gameNumber}: $matches matches => $gameScore points");
}

echo $totalScore . "\n";
