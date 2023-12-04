<?php
declare(strict_types=1);

namespace AoC\Four;

use AoC\Common\InputLoader;

require_once __DIR__ . '/../../common/php/InputLoader.php';

final class Card
{
    private ?int $matchCount = null;

    public function __construct(
        public readonly int $cardNumber,
        public readonly array $winningNumbers,
        public readonly array $yourNumbers
    )  {}

    public function getMatchCount(): int {
        if (!isset($this->matchCount)) {
            $matchCount = 0;
            foreach ($this->yourNumbers as $yourNumber) {
                if (in_array($yourNumber, $this->winningNumbers)) {
                    $matchCount += 1;
                }
            }
            $this->matchCount = $matchCount;
        }
        return $this->matchCount;
    }

    public static function fromInputLine(string $line): self
    {
        // Card 6: 31 18 13 56 72 | 74 77 10 23 35 67 36 11
        [$label, $numbers] = explode(': ', $line);
        [$discard, $gameNumber] = preg_split('/ +/', $label);
        [$winningNumberString, $yourNumberString] = explode(' | ', $numbers);

        $winningNumberStrings = preg_split('/ +/', trim($winningNumberString));
        $yourNumberStrings = preg_split('/ +/', trim($yourNumberString));

        $winningNumbers = array_map(fn(string $x) => (int) $x, $winningNumberStrings);
        $yourNumbers = array_map(fn(string $x) => (int) $x, $yourNumberStrings);

        return new Card((int) $gameNumber, $winningNumbers, $yourNumbers);
    }
}

/**
 * @return Card[]
 */
function getCards(): array
{
    $lines = (new InputLoader(__DIR__))->getAsStrings();

    $games = [];
    foreach ($lines as $line) {
        $games[]= Card::fromInputLine($line);
    }

    return $games;
}
