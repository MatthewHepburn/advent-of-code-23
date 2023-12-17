<?php
declare(strict_types=1);

namespace AoC\Seventeen;

use AoC\Common\AdjacenyGenerator2D;
use AoC\Common\InputLoader;
use AoC\Common\Logger;
use function AoC\Common\filter;

require_once __DIR__ . '/../../common/php/autoload.php';

enum Direction: string
{
    case North = 'north';
    case East = 'east';
    case South = 'south';
    case West = 'west';
}

interface Costs {
    public function getBestCost(): ?int;
    public function recordCost(Direction $direction, int $steps, int $cost): bool;
}
final class CrucibleCosts implements Costs
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

    public function getBestCost(): ?int
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
        $filteredVals = array_filter($values);
        return $filteredVals ? min(...$filteredVals) : null;
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

final class UltraCrucibleCosts implements Costs
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
    public Costs $costs;

    public function __construct(public readonly int $cost, bool $ultra)
    {
        $this->costs = $ultra ? new UltraCrucibleCosts() : new CrucibleCosts();
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

    public function getUltraCrucibleMoveIn(Direction $direction, AdjacenyGenerator2D $adjacenyGenerator)
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

        if ($direction === $this->direction && $this->movesInDirection >= 10) {
            // Can't move there, we need to change direction
            return null;
        }
        if ($direction !== $this->direction && $this->movesInDirection < 4) {
            // Can't move there, we can't change direction yet
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
    public readonly GridSquare $endSquare;

    /**
     * @param string[][] $points
     */
    public function __construct(
        array $points,
        bool $ultra = false
    ) {
        $this->squares = [];
        foreach ($points as $row) {
            $this->squares[]= array_map(fn(string $x) => new GridSquare((int) $x, $ultra), $row);
        }
        $this->endSquare = $this->squares[count($this->squares) - 1][count($this->squares[0]) - 1];

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
            new JourneyState(Direction::East, 0, 0, 0, 0),
            new JourneyState(Direction::South, 0, 0, 0, 0)
        ];

        do {
            $frontier = $this->doSearchIteration($frontier);
            $this->logger?->log("Frontier has "  . count($frontier) . " states");

            $endCost = $this->endSquare->costs->getBestCost();
            if ($endCost !== null) {
                $this->logger?->log("Found end cost $endCost");
                $frontier = filter($frontier, fn(JourneyState $s) => $s->cost < $endCost);
                $this->logger?->log("We have an end cost, filtered down options to " . count($frontier));
            }
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

    public function searchUltraCrucible(): void {
        $frontier = [
            new JourneyState(Direction::East, 0, 0, 0, 0),
            new JourneyState(Direction::South, 0, 0, 0, 0)
        ];

        do {
            $frontier = $this->doSearchIterationUltraCrucible($frontier);
            $this->logger?->log("Frontier has "  . count($frontier) . " states");

            $endCost = $this->endSquare->costs->getBestCost();
            if ($endCost !== null) {
                $frontier = filter($frontier, fn(JourneyState $s) => $s->cost < $endCost);
                $this->logger?->log("We have an end cost, filtered down options to " . count($frontier));
            }
        } while ($frontier);
    }

    /**
     * @param JourneyState[] $frontier
     *
     * @return JourneyState[]
     */
    private function doSearchIterationUltraCrucible(array $frontier): array
    {
        $newFrontier = [];
        foreach ($frontier as $state) {
            foreach (Direction::cases() as $direction) {
                $dest = $state->getUltraCrucibleMoveIn($direction, $this->adjacenyGenerator);
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

function getMap(bool $ultra = false): StreetMap
{
    $input = (new InputLoader(__DIR__))->getAsCharArray();
    return new StreetMap($input, $ultra);
}

