<?php
declare(strict_types=1);

namespace AoC\Seven;

use AoC\Common\InputLoader;
use function AoC\Common\filter;

require_once __DIR__ . '/../../common/php/InputLoader.php';
require_once __DIR__ . '/../../common/php/StandardLib.php';


Enum Face: string
{
    case Ace = 'A';
    case King = 'K';
    case Queen = 'Q';
    case Jack = 'J';
    case Ten = 'T';
    case Nine = '9';
    case Eight = '8';
    case Seven = '7';
    case Six = '6';
    case Five = '5';
    case Four = '4';
    case Three = '3';
    case Two = '2';
    case Joker = '?';

    public function getStrength(): int {
        return match ($this) {
            self::Ace => 14,
            self::King => 13,
            self::Queen => 12,
            self::Jack => 11,
            self::Ten => 10,
            self::Joker => 1,
            default => (int) $this->value
        };
    }
}

Enum HandType: int {
    case FiveOfAKind = 7;
    case FourOfAKind = 6;
    case FullHouse = 5;
    case ThreeOfAKind = 4;
    case TwoPair = 3;
    case Pair = 2;
    case HighCard = 1;

    public static function fromCount(array $valueCount): self {
        if (count($valueCount) === 1) {
            return HandType::FiveOfAKind;
        }
        if (in_array(4, $valueCount, true)) {
            return HandType::FourOfAKind;
        }
        if (in_array(3, $valueCount, true) && in_array(2, $valueCount)) {
            return HandType::FullHouse;
        }
        if (in_array(3, $valueCount, true)) {
            return HandType::ThreeOfAKind;
        }
        if (in_array(2, $valueCount, true)) {
            // Have at least one pair. Do we have another?
            $pairs = count(filter($valueCount, fn(int $count) => $count === 2));
            if ($pairs > 1) {
                return HandType::TwoPair;
            }
            return HandType::Pair;
        }

        return HandType::HighCard;
    }
}

final readonly class Hand {
    public HandType $handType;

    /**
     * @param Face[] $hand
     */
    public function __construct(
        private readonly array $hand,
        public readonly int $bid
    ) {
        $this->handType = $this->calculateHandType();
    }

    public static function fromLine(string $line, bool $jokers): self
    {
        if ($jokers) {
            $line = str_replace('J', '?', $line);
        }
        [$cardsString, $bidString] = explode(' ', $line);
        $cards = [];
        foreach (str_split($cardsString) as $card) {
            $cards[] = Face::from($card);
        }

        return new Hand($cards, (int) $bidString);
    }

    public function getCardAt(int $i): Face
    {
        return $this->hand[$i];
    }

    public function __toString(): string
    {
        $output = '';
        foreach ($this->hand as $card) {
            $output .= $card->value;
        }
        return "$output $this->bid";
    }

    private function calculateHandType(): HandType
    {
        $valueCount = [];
        foreach ($this->hand as $card) {
            $valueCount[$card->name] = $valueCount[$card->name] ?? 0;
            $valueCount[$card->name] += 1;
        }

        if (!isset($valueCount['Joker'])) {
            // No Joker, evaluate directly
            return HandType::fromCount($valueCount);
        }

        echo "before = " . json_encode($valueCount) . "\n";

        // Change the Jokers to match the card with the highest count
        $jokerCount = $valueCount['Joker'];
        unset($valueCount['Joker']);
        $highestFace = null;
        foreach ($valueCount as $face => $count) {
            $highestFace ??= $face;
            $highestFace = $count > $valueCount[$highestFace] ? $face : $highestFace;
        }

        $valueCount[$highestFace] += $jokerCount;

        echo "after = " . json_encode($valueCount) . "\n";

        return HandType::fromCount($valueCount);
    }
}

/**
 * @return Hand[];
 */
function getHands(bool $jokers = false): array
{
    $lines = (new InputLoader(__DIR__))->getAsStrings();
    $output = [];
    foreach ($lines as $line) {
        $output []= Hand::fromLine($line, $jokers);
    }

    return $output;
}
