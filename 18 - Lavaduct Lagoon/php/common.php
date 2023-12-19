<?php
declare(strict_types=1);

namespace AoC\Eighteen;

use AoC\Common\AdjacenyGenerator2D;
use AoC\Common\InputLoader;
use AoC\Common\Logger;

require_once __DIR__ . '/../../common/php/autoload.php';

enum Direction: string
{
    case Up = 'U';
    case Down = 'D';
    case Left = 'L';
    case Right = 'R';

    public function moveInDirection(int $i, int $j, int $distance): array {
        return match ($this) {
            Direction::Up => [$i - $distance, $j],
            Direction::Down => [$i + $distance, $j],
            Direction::Left => [$i, $j - $distance],
            Direction::Right => [$i, $j + $distance],
        };
    }

    public function stepInDirection(int $i, int $j): array {
        return $this->moveInDirection($i, $j, 1);
    }
}

final readonly class PlanStep
{
    public function __construct(
        public Direction $direction,
        public int $distance,
        public string $edgeColour
    ) {}

    public static function fromLine(string $line): self
    {
        [$dir, $dist, $col] = explode(' ', $line);
        $direction = Direction::from($dir);
        $distance = (int) $dist;
        $colourCode = trim($col, '()');

        return new PlanStep($direction, $distance, $colourCode);
    }

    public function getNewPosition(int $i, int $j): array
    {
        return match ($this->direction) {
            Direction::Up => [$i - $this->distance, $j],
            Direction::Down => [$i + $this->distance, $j],
            Direction::Left => [$i, $j - $this->distance],
            Direction::Right => [$i, $j + $this->distance],
        };
    }
}

final class Vertex {
    public ?Direction $lastDirection = null;
    public ?Direction $nextDirection = null;
    public readonly array $endPoint;

    public function __construct(
        public readonly array $startPoint,
        public readonly Direction $direction,
        public readonly int $length
    ) {
        $this->endPoint = $this->direction->moveInDirection($this->startPoint[0], $this->startPoint[1],  $this->length);
    }

    public function intersectsVertical(int $i): bool
    {
        return ($this->startPoint[0] <= $i && $i <= $this->endPoint[0]) || ($this->endPoint[0] <= $i && $i <= $this->startPoint[0]);
    }

    public function pointConnectsTo(int $i, int $j): bool {
        return ($this->startPoint[0] === $i && $this->startPoint[1] === $j) || ($this->endPoint[0] === $i && $this->endPoint[1] === $j);
    }

    public function isProtrusion(): bool
    {
        if (!$this->lastDirection || !$this->nextDirection) {
            throw new \Exception("Missing direction from Vertex");
        }

        // If we entered going up and exited going down, we're a protrusion
        return $this->lastDirection !== $this->nextDirection;
    }

    public function isVertical(): bool
    {
        return $this->direction === Direction::Up || $this->direction === Direction::Down;
    }

    public function __toString(): string
    {
        $last = $this->lastDirection?->value ?? '?';
        $next = $this->nextDirection?->value ?? '?';
        return "[$last ({$this->startPoint[0]}, {$this->startPoint[1]}) {$this->direction->value}{$this->length}-> ({$this->endPoint[0]}, {$this->endPoint[1]}) $next]";
    }
}

final class MapPoint
{
    public bool $isInside = true;
    public bool $isExcavated = false;

    public function __toString(): string
    {
        return $this->isExcavated ? '#' : ' ';
    }

    public function toPoolString(): string
    {
        return ($this->isExcavated || $this->isInside) ? '#' : ' ';
    }
}

final class ExcavationMap
{
    /** @var MapPoint[][] */
    public array $points;
    /** @var Vertex[] */
    public array $horizontalVertices = [];
    /** @var Vertex[] */
    public array $verticalVertices = [];
    public ?Logger $logger;
    public readonly array $startPoint;
    public AdjacenyGenerator2D $adjacenyGenerator;

    public function __construct(MapSpec $mapSpec)
    {
        $this->points = [];
        for ($i = 0; $i <= $mapSpec->height; $i++) {
            $row = [];
            for ($j = 0; $j <= $mapSpec->width; $j++) {
                $row[]= new MapPoint();
            }
            $this->points[]= $row;
        }

        $this->startPoint = [$mapSpec->startI, $mapSpec->startJ];

        $this->adjacenyGenerator = new AdjacenyGenerator2D(0, 0, count($this->points) - 1, count($this->points[0]) - 1, false);
    }

    public function getPointAt(int $i, int $j): MapPoint
    {
        return $this->points[$i][$j];
    }

    /**
     * @param PlanStep[] $steps
     *
     * @return void
     */
    public function followPlan(array $steps): void
    {
        $position = $this->startPoint;
        $firstVertex = null;
        $lastVertex = null;
        $lastDirection = null;
        foreach ($steps as $step) {
            $vertex = new Vertex($position, $step->direction, $step->distance);
            $position = $vertex->endPoint;

            if ($lastDirection) {
                $vertex->lastDirection = $lastDirection;
            } else {
                $firstVertex = $vertex;
            }
            if ($lastVertex) {
                $lastVertex->nextDirection = $step->direction;
            }

            $lastDirection = $step->direction;
            $lastVertex = $vertex;

            if ($vertex->isVertical()) {
                $this->verticalVertices[]= $vertex;
            } else {
                $this->horizontalVertices[]= $vertex;
            }
            $this->logger?->log("Line: $vertex");
        }
        $firstVertex->lastDirection = $step->direction;
        $lastVertex->nextDirection = $firstVertex->direction;

        usort($this->verticalVertices, fn(Vertex $a, Vertex $b) => $a->startPoint[1] <=> $b->startPoint[1]);
        usort($this->horizontalVertices, fn(Vertex $a, Vertex $b) => $a->startPoint[0] <=> $b->startPoint[0]);
    }

    public function markInner(): int
    {
        $total = 0;
        foreach ($this->verticalVertices as $verticalVertex) {
            $total += $verticalVertex->length;
        }
        foreach ($this->horizontalVertices as $horizontalVertex) {
            $total += $horizontalVertex->length;
        }
        $this->logger?->log("Total directly excavated: $total");
        $enclosedByRow = [];

        for ($i = 0; $i < count($this->points); $i++) {
            $this->logger?->log("----Row $i-----");
            $j = 0;
            $inTrench = false;
            $rowTotal = 0;
            $horizontalIndex = 0;
            foreach ($this->verticalVertices as $verticalVertex) {
                if (!$verticalVertex->intersectsVertical($i)) {
                    continue;
                }
                $this->logger?->log("Intersecting with vertical line $verticalVertex from ($i, $j). RowTotal = $rowTotal");
                $moved = $verticalVertex->endPoint[1] - $j;
                if ($moved === 0) {
                    continue;
                }
                $j = $j + $moved;
                if ($inTrench) {
                    $rowTotal += $moved - 1;
                }
                $this->logger?->log("    ->Intersected with vertical line $verticalVertex, now at ($i, $j). RowTotal = $rowTotal");

                // Have we hit a corner?
                for (; $horizontalIndex < count($this->horizontalVertices); $horizontalIndex++) {
                    $horizontalVertex = $this->horizontalVertices[$horizontalIndex];
                    if ($horizontalVertex->pointConnectsTo($i, $j)) {
                        $this->logger?->log("Point ($i, $j) lies on horizontal line $horizontalVertex");

                        // Follow the vertex along to the right
                        $rightEnd = max($horizontalVertex->endPoint[1], $horizontalVertex->startPoint[1]);
                        $moved = $rightEnd - $j;
                        if ($moved === 0) {
                            continue;
                        }
                        $j = $rightEnd;
                        $this->logger?->log("Hit corner, followed line $moved to ($i, $j)");
                        if (!$horizontalVertex->isProtrusion()) {
                            $this->logger?->log("  NOT protrusion, changing trench state. RowTotal = $rowTotal");
                            $inTrench = !$inTrench;
                        }
                        $this->logger?->log("  was protrusion, not changing trench state. RowTotal = $rowTotal");
                        continue 2;
                    }
                }

                $this->logger?->log("No corner found");
                // Didn't hit a corner, just a simple boundary
                $inTrench = !$inTrench;
            }

            $this->logger?->log("Row total for row $i = $rowTotal");
            $enclosedByRow[$i] = $rowTotal;
            $total += $rowTotal;
        }

        echo json_encode($enclosedByRow);

        return $total;
    }

    public function getPoolSize(): int
    {
        $total = 0;
        foreach ($this->points as $row) {
            foreach ($row as $point) {
                if ($point->isExcavated || $point->isInside) {
                    $total += 1;
                }
            }
        }

        return $total;
    }

    public function getOutlineDiagram(): string
    {
        $output = '';
        foreach ($this->points as $row) {
            $output .= implode('', $row) . "\n";
        }

        return $output;
    }

    public function getPoolDiagram(): string
    {
        $output = '';
        foreach ($this->points as $row) {
            $row = array_map(fn(MapPoint $p) => $p->toPoolString(), $row);
            $output .= implode('', $row) . "\n";
        }

        return $output;
    }
}

/**
 * @return PlanStep[]
 */
function getSteps(): array
{
    $input = (new InputLoader(__DIR__))->getAsStrings();
    return array_map(fn(string $x) => PlanStep::fromLine($x), $input);
}

final readonly class MapSpec
{
    public function __construct(
        public int $width,
        public int $height,
        public int $startI,
        public int $startJ
    ){}

    public function __toString(): string
    {
        return "h:{$this->height}, w={$this->width}, start=({$this->startI}, {$this->startJ})";
    }
}

/**
 * @param PlanStep[] $steps
 */
function getMapSpec(array $steps): MapSpec
{
    $minI = $minJ = $maxI = $maxJ = $i = $j = 0;
    foreach ($steps as $step) {
        [$i, $j] = $step->getNewPosition($i, $j);
        $minI = min($i, $minI);
        $minJ = min($minJ, $j);
        $maxI = max($maxI, $i);
        $maxJ = max($maxI, $j);
    }

    // Padding - shouldn't need this, but easier than working our where we've gone wrong
    $height = 10 + $maxI - $minI;
    $width = 10 + $maxJ - $minJ;
    $startI = abs($minI) + 1;
    $startJ = abs($minJ) + 1;

    return new MapSpec($width, $height, $startI, $startJ);
}

