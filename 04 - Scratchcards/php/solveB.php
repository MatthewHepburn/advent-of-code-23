<?php
declare(strict_types=1);

namespace AoC\Four;

use AoC\Common\Logger;

require_once __DIR__ . '/common.php';
require_once __DIR__ . '/../../common/php/Logger.php';

$logger = new Logger();

$cards = getCards();
$cardMap = [];
foreach ($cards as $card) {
    $cardMap[$card->cardNumber] = $card;
}

/**
 * @param Card $input
 *
 * @return Card[]
 */
$getWinnings = function (Card $input) use ($cardMap, $logger) : array {
    $matches = $input->getMatchCount();
    $output = [];
    for ($match = 1; $match <= $matches; $match++) {
        $output[]= $cardMap[$input->cardNumber + $match];
    }

    $wonCardNumbers = array_map(fn(Card $c) => $c->cardNumber, $output);
    if ($output) {
        $logger->log("Won " . json_encode($wonCardNumbers) . " from card {$input->cardNumber}");
    }

    return $output;
};

$cardsSeen = 0;
$myCards = $cards;
while ($myCards) {
    $nextCard = array_pop($myCards);
    $cardsSeen += 1;
    $newCards = $getWinnings($nextCard);
    array_push($myCards, ...$newCards);
}

echo $cardsSeen . "\n";
