<?php
declare(strict_types=1);

namespace AoC\Seventeen;

use AoC\Common\AdjacenyGenerator2D;
use AoC\Common\InputLoader;
use AoC\Common\Logger;

require_once __DIR__ . '/../../common/php/autoload.php';

enum Direction: string
{
    case North = 'north';
    case East = 'east';
    case South = 'south';
    case West = 'west';
}

final class GridCosts
{
    public ?int $north1 = null;
    public ?int $north2 = null;
    public ?int $north3 = null;
    public ?int $east1 = null;
    public ?int $east2 = null;
    public ?int $east3 = null;
    public ?int $south1 = null;
    public ?int $south2 = null;
    public ?int $south3 = null;
    public ?int $west1 = null;
    public ?int $west2 = null;
    public ?int $west3 = null;

    public function getBestCost(): int
    {
        $values = [
            $this->north1,
            $this->north2,
            $this->north3,
            $this->east1,
            $this->east2,
            $this->east3,
            $this->south1,
            $this->south2,
            $this->south3,
            $this->west1,
            $this->west2,
            $this->west3,
        ];
        return min(...array_filter($values));
    }

    public function recordCost(Direction $direction, int $steps, int $cost): bool {
        $key = "{$direction->value}{$steps}";
        $improved = $this->$key === null || $cost < $this->$key;
        if ($improved) {
            $this->$key = $cost;
        }

        return $improved;
    }
}
final class GridSquare
{
    public bool $isStart = false;
    public bool $isEnd = false;
    public GridCosts $costs;

    public function __construct(public readonly int $cost)
    {
        $this->costs = new GridCosts();
    }
}

final readonly class JourneyState {
    public function __construct(
        public Direction $direction,
        public int $cost,
        public int $movesInDirection,
        public int $i,
        public int $j
    ) {}

    public function getMoveIn(Direction $direction, AdjacenyGenerator2D $adjacenyGenerator): ?array
    {
        $reverse = match ($this->direction) {
            Direction::North => Direction::South,
            Direction::East => Direction::West,
            Direction::South => Direction::North,
            Direction::West => Direction::East
        };
        if ($direction === $reverse) {
            return null;
        }

        $dest = match ($direction) {
            Direction::North => $adjacenyGenerator->getUp($this->i, $this->j),
            Direction::East => $adjacenyGenerator->getRight($this->i, $this->j),
            Direction::South => $adjacenyGenerator->getDown($this->i, $this->j),
            Direction::West => $adjacenyGenerator->getLeft($this->i, $this->j)
        };
        if (!$dest) {
            // Can't move there, it's out of bounds
            return null;
        }

        if ($direction === $this->direction && $this->movesInDirection >= 3) {
            // Can't move there, we need to change direction
            return null;
        }

        return $dest;
    }
}

final class StreetMap
{
    public ?Logger $logger = null;
    /** @var GridSquare[][] */
    public array $squares;
    public AdjacenyGenerator2D $adjacenyGenerator;

    /**
     * @param string[][] $points
     */
    public function __construct(
        array $points
    ) {
        $this->squares = [];
        foreach ($points as $row) {
            $this->squares[]= array_map(fn(string $x) => new GridSquare((int) $x), $row);
        }
        $this->squares[0][0]->isStart = true;
        $this->squares[count($this->squares) - 1][count($this->squares[0]) - 1]->isEnd = true;

        $this->adjacenyGenerator = new AdjacenyGenerator2D(
            0,
            0,
            count($this->squares) - 1,
            count($this->squares[0]) - 1,
            false
        );
    }

    public function search(): void {
        $frontier = [
            new JourneyState(Direction::East, 0, 0, 0, 0)
        ];

        do {
            $frontier = $this->doSearchIteration($frontier);
            $this->logger?->log("Frontier has "  . count($frontier) . " states");
        } while ($frontier);
    }

    /**
     * @param JourneyState[] $frontier
     *
     * @return JourneyState[]
     */
    private function doSearchIteration(array $frontier): array
    {
        $newFrontier = [];
        foreach ($frontier as $state) {
            foreach (Direction::cases() as $direction) {
                $dest = $state->getMoveIn($direction, $this->adjacenyGenerator);
                if (!$dest) {
                    continue;
                }
                [$destI, $destJ] = $dest;
                $destSquare = $this->squares[$destI][$destJ];
                $cost = $state->cost + $destSquare->cost;
                $steps = $direction === $state->direction ? $state->movesInDirection + 1 : 1;
                $improvement = $destSquare->costs->recordCost($direction, $steps, $cost);
                if ($improvement) {
                    $newFrontier[]= new JourneyState($direction, $cost, $steps, $destI, $destJ);
                }
            }
        }

        return $newFrontier;
    }

    public function getBestCostDiagram(): string
    {
        $output = '';
        foreach ($this->squares as $row) {
            $rowCosts = array_map(fn(GridSquare $s) => "[" . str_pad((string)$s->costs->getBestCost(), 2, ' ') . "]", $row);
            $output .= implode('', $rowCosts) . "\n";
        }

        return $output;
    }
}

function getMap(): StreetMap
{
    $input = (new InputLoader(__DIR__))->getAsCharArray();
    return new StreetMap($input);
}

