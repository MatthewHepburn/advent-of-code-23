<?php

declare(strict_types=1);

require_once __DIR__ . '/../../common/php/InputLoader.php';

enum Colour {
    case RED;
    case BLUE;
    case GREEN;

    public static function fromString(string $x): self {
        return match ($x) {
            'red' => self::RED,
            'blue' => self::BLUE,
            'green' => self::GREEN,
            default => throw new Exception("Unexpected colour: $x")
        };
    }
}
readonly class Action {
    public function __construct(
        public Colour $colour,
        public int $count
    ) {}
}

readonly class Game {
    /**
     * @param int $id
     * @param Action[][] $rounds
     */
    public function __construct (
        public int $id,
        public array $rounds
    ) {}

    public function toString(): string {
        $output = "Game: {$this->id}: ";
        foreach ($this->rounds as $round) {
            $output .= '[';
            foreach ($round as $action) {
                $output .= $action->colour->name . ':' . $action->count . ',';
            }
            $output .= '], ';
        }

        return $output;
    }
}

function getInput(): array {
    $lines = (new InputLoader(__DIR__))->getAsStrings();

    $games = [];
    foreach ($lines as $line) {
        [$idString, $gameString] = explode(':', $line);
        [$discard, $gameId] = explode(' ', $idString);
        $roundStrings = explode(';', $gameString);
        $rounds = [];
        foreach ($roundStrings as $roundString) {
            $roundString = trim($roundString);
            $actions = [];
            $actionStrings = explode(', ', $roundString);
            foreach ($actionStrings as $actionString) {
                [$count, $colour] = explode(' ', $actionString);
                $actions[] = new Action(Colour::fromString($colour), (int) $count);
            }
            $rounds[]= $actions;
        }
        $games[]= new Game((int) $gameId, $rounds);
    }

    return $games;
}


