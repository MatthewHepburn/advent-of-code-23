<?php
declare(strict_types=1);

namespace AoC\Eight;

use AoC\Common\InputLoader;

require_once __DIR__ . '/../../common/php/InputLoader.php';
require_once __DIR__ . '/../../common/php/StandardLib.php';

Enum Direction: string {
    case Left = 'L';
    case Right = 'R';
}

final readonly class Node {
    public function __construct(
        public string $name,
        private string $left,
        private string $right
    ) {}

    public function getNodeNameInDirection(Direction $direction) {
        return match ($direction) {
            Direction::Left => $this->left,
            Direction::Right => $this->right
        };
    }

    public static function fromString(string $x): self
    {
        // String like 'AAA = (BBB, BBB)'
        [$label, $directions] = explode(' = ', $x);
        $directions = trim($directions, '()');
        [$left, $right] = explode(', ', $directions);

        return new self($label, $left, $right);
    }
}

final readonly class Problem {
    /**
     * @var array<string,Node>
     */
    public array $nodesByName;

    /**
     * @param Direction[] $directions
     * @param Node[] $nodes
     */
    public function __construct(
        public array $directions,
        array $nodes
    )
    {
        $nodesByName = [];
        foreach ($nodes as $node) {
            $nodesByName[$node->name] = $node;
        }
        $this->nodesByName = $nodesByName;
    }

    public static function fromInputLines(array $lines): self
    {
        $directionsLine = $lines[0];
        $directions = array_map(fn(string $x) => Direction::from($x), str_split($directionsLine));
        unset($lines[0]);

        $nodes = array_map(fn(string $x) => Node::fromString($x), $lines);

        return new Problem($directions, $nodes);
    }
}

function getProblem(): Problem
{
    $lines = (new InputLoader(__DIR__))->getAsStrings();
    return Problem::fromInputLines($lines);
}
