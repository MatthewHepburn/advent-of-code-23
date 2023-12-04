<?php
declare(strict_types=1);

namespace AoC\Four;

use AoC\Common\InputLoader;

require_once __DIR__ . '/../../common/php/InputLoader.php';

final readonly class Game
{
    public function __construct(
        public int $gameNumber,
        public array $winningNumbers,
        public array $yourNumbers
    )  {}

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

        return new Game((int) $gameNumber, $winningNumbers, $yourNumbers);
    }
}

/**
 * @return Game[]
 */
function getGames(): array
{
    $lines = (new InputLoader(__DIR__))->getAsStrings();

    $games = [];
    foreach ($lines as $line) {
        $games[]= Game::fromInputLine($line);
    }

    return $games;
}
