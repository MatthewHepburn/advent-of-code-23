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
    $logger->log($hand . " => " . $hand->strength . ", wins {$hand->bid} x $rank = " . $wins);
    $winnings += $wins;
    $rank += 1;
}

echo $winnings . "\n";

/**
 * So, the first step is to put the hands in order of strength:
 *
 * 32T3K is the only one pair and the other hands are all a stronger type, so it gets rank 1.
 * KK677 and KTJJT are both two pair. Their first cards both have the same label, but the second card of KK677 is stronger (K vs T), so KTJJT gets rank 2 and KK677 gets rank 3.
 * T55J5 and QQQJA are both three of a kind. QQQJA has a stronger first card, so it gets rank 5 and T55J5 gets rank 4.
 *
 * Now, you can determine the total winnings of this set of hands by adding up the result of multiplying each hand's bid with its rank (765 * 1 + 220 * 2 + 28 * 3 + 684 * 4 + 483 * 5). So the total winnings in this example are 6440.
 */
