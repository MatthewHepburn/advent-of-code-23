<?php
declare(strict_types=1);

namespace AoC\Seven;

use AoC\Common\Logger;

require_once __DIR__ . '/common.php';
require_once __DIR__ . '/../../common/php/Logger.php';

$logger = new Logger();

$hands = getHands();

$compare = function (Hand $a, Hand $b) {
    if ($a->handType !== $b->handType) {
        return $a->handType->value <=> $b->handType->value;
    }
    for ($i = 0; $i < 5; $i++) {
        $aCard = $a->getCardAt($i);
        $bCard = $b->getCardAt($i);
        if ($aCard !== $bCard) {
            return $aCard->getStrength() <=> $bCard->getStrength();
        }
    }

    throw new \Exception('Hands are the same, cannot compare');
};

usort($hands, $compare);

$winnings = 0;
$rank = 1;
foreach ($hands as $hand) {
    $wins = $hand->bid * $rank;
    $logger->log($hand . " =>  wins {$hand->bid} x $rank = " . $wins);
    $winnings += $wins;
    $rank += 1;
}

echo $winnings . "\n";
