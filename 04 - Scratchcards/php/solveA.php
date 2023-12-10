<?php
declare(strict_types=1);

namespace AoC\Four;

use AoC\Common\Logger;

require_once __DIR__ . '/common.php';
require_once __DIR__ . '/../../common/php/autoload.php';

$logger = new Logger();

$totalScore = 0;
foreach (getCards() as $card) {
    $logger->log("Card: {$card->cardNumber}: your numbers = " . json_encode($card->yourNumbers) . ", winning = " . json_encode($card->winningNumbers));
    $matches = $card->getMatchCount();
    $gameScore = $matches ? pow(2, $matches - 1) : 0;
    $totalScore += $gameScore;
    $logger->log("    Card {$card->cardNumber}: $matches matches => $gameScore points");
}

echo $totalScore . "\n";
