<?php
declare(strict_types=1);

namespace AoC\Eighteen;

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
        $colourCode = trim($col, '(#)');

        return new PlanStep($direction, $distance, $colourCode);
    }

    public function getCorrected(): self
    {
        $hexChars = str_split($this->edgeColour);
        $lastChar = array_pop($hexChars);
        $direction = match ($lastChar) {
            '0' => Direction::Right,
            '1' => Direction::Down,
            '2' => Direction::Left,
            '3' => Direction::Up
        };
        $distance = hexdec(implode('', $hexChars));

        return new PlanStep($direction, $distance, '');
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

    public function __toString(): string
    {
        return "{$this->direction->value} {$this->distance}";
    }
}

final class Vertex {
    public ?Direction $lastDirection = null;
    public ?Direction $nextDirection = null;
    public ?Vertex $lastVertex = null;
    public ?Vertex $nextVertex = null;
    public readonly array $endPoint;

    public function __construct(
        public readonly array $startPoint,
        public readonly Direction $direction,
        public readonly int $length
    ) {
        $this->endPoint = $this->direction->moveInDirection($this->startPoint[0], $this->startPoint[1],  $this->length);
    }

    public function extendsToRight(int $i, int $j): bool
    {
        return $this->endPoint[1] > $j || $this->startPoint[1] > $j;
    }

    public function getAdjoiningVertex(int $i, int $j): ?self
    {
        if ($i === $this->startPoint[0] && $j === $this->startPoint[1]) {
            return $this->lastVertex;
        }
        if ($i === $this->endPoint[0] && $j === $this->endPoint[1]) {
            return $this->nextVertex;
        }

        return null;
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
        if (!$this->lastVertex || !$this->nextVertex) {
            throw new \Exception("Missing connections from Vertex");
        }

        // If we entered going up and exited going down, we're a protrusion
        return $this->lastVertex->direction !== $this->nextVertex->direction;
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
    public bool $isInside = false;
    public bool $isExcavated = false;
    public ?Direction $excavationDirection = null;

    public function __toString(): string
    {
        return $this->isExcavated ? '#' : ' ';
    }

    public function toPoolString(): string
    {
        if ($this->isInside && $this->isExcavated) {
            return '!';
        }
        if ($this->excavationDirection) {
            return match ($this->excavationDirection) {
                Direction::Up => '^',
                Direction::Down => 'v',
                Direction::Left => '<',
                Direction::Right => '>'
            };
        }
        if ($this->isExcavated) {
            return '#';
        }
        if ($this->isInside) {
            return '+';
        }
        return ' ';
    }
}

final class ExcavationMap
{
    /** @var MapPoint[][] */
    public array $points;
    public int $rows;
    /** @var Vertex[] */
    public array $horizontalVertices = [];
    /** @var Vertex[] */
    public array $verticalVertices = [];
    public ?Logger $logger;
    public readonly array $startPoint;

    public function __construct(MapSpec $mapSpec, private readonly bool $visualise)
    {
        $this->points = [];
        if ($visualise) {
            for ($i = 0; $i <= $mapSpec->height; $i++) {
                $row = [];
                for ($j = 0; $j <= $mapSpec->width; $j++) {
                    $row[]= new MapPoint();
                }
                $this->points[]= $row;
            }
        }

        $this->rows = $mapSpec->height;
        $this->startPoint = [$mapSpec->startI, $mapSpec->startJ];
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
                $vertex->lastVertex = $lastVertex;
            } else {
                $firstVertex = $vertex;
            }
            if ($lastVertex) {
                $lastVertex->nextDirection = $step->direction;
                $lastVertex->nextVertex = $vertex;
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
        $firstVertex->lastVertex = $lastVertex;
        $lastVertex->nextDirection = $firstVertex->direction;
        $lastVertex->nextVertex = $firstVertex;

        usort($this->verticalVertices, fn(Vertex $a, Vertex $b) => $a->startPoint[1] <=> $b->startPoint[1]);
        usort($this->horizontalVertices, fn(Vertex $a, Vertex $b) => $a->startPoint[0] <=> $b->startPoint[0]);
    }

    public function markInner(): int
    {
        $total = 0;
        foreach ($this->verticalVertices as $verticalVertex) {
            $this->markVertexExcavated($verticalVertex);
            $total += $verticalVertex->length;
        }
        foreach ($this->horizontalVertices as $horizontalVertex) {
            $this->markVertexExcavated($horizontalVertex);
            $total += $horizontalVertex->length;
        }
        $this->logger?->log("Total directly excavated: $total");

        for ($i = 0; $i < $this->rows; $i++) {
            $this->logger?->log("----Row $i-----");
            $j = 0;
            $inTrench = false;
            $rowTotal = 0;
            $sawHorizontal = false;
            foreach ($this->verticalVertices as $verticalVertex) {
                if (!$verticalVertex->intersectsVertical($i)) {
                    continue;
                }
                $this->logger?->log("Intersecting with vertical line $verticalVertex from ($i, $j). RowTotal = $rowTotal");
                $moved = $verticalVertex->endPoint[1] - $j;
                if ($moved === 0) {
                    continue;
                }
                if ($inTrench) {
                    $this->logger?->log("In trench, moving $moved");
                    if ($this->visualise) {
                        for ($a = 1; $a < $moved; $a++) {
                            $this->getPointAt($i, $j + $a)->isInside = true;
                        }
                    }
                    $rowTotal += $moved - 1;
                }
                $j = $j + $moved;

                $this->logger?->log("    ->Intersected with vertical line $verticalVertex, now at ($i, $j). RowTotal = $rowTotal");

                $horizontalVertex = $verticalVertex->getAdjoiningVertex($i, $j);
                if ($horizontalVertex && $horizontalVertex->extendsToRight($i, $j)) {
                    $sawHorizontal = true;
                    $this->logger?->log("Point ($i, $j) lies on horizontal line $horizontalVertex");

                    // Follow the vertex along to the right
                    $rightEnd = max($horizontalVertex->endPoint[1], $horizontalVertex->startPoint[1]);
                    $moved = $rightEnd - $j;
                    $j = $rightEnd;
                    $this->logger?->log("Hit corner, followed line $moved to ($i, $j)");
                    if (!$horizontalVertex->isProtrusion()) {
                        $this->logger?->log("  NOT protrusion, changing trench state. RowTotal = $rowTotal");
                        $inTrench = !$inTrench;
                        continue;
                    } else {
                        $this->logger?->log("  was protrusion, not changing trench state. RowTotal = $rowTotal");
                        continue;
                    }
                }

                $this->logger?->log("No corner found");
                // Didn't hit a corner, just a simple boundary
                $inTrench = !$inTrench;
            }

            $this->logger?->log("Row total for row $i = $rowTotal");
            $total += $rowTotal;

            if (!$sawHorizontal) {
                // No horizontal lines on this row, so skip ahead to the next landmark
                $nextI = null;
                foreach ($this->horizontalVertices as $horizontalVertex) {
                    if ($horizontalVertex->startPoint[0] > $i) {
                        $nextI = $nextI ? min($horizontalVertex->startPoint[0], $nextI) : $horizontalVertex->startPoint[0];
                    }
                }

                if ($nextI && $nextI > $i + 1) {
                    // Jump to 1 before, since we're about to hit an i++
                    $nextI = $nextI - 1;
                    $jump = $nextI - $i;
                    $skippedRowTotal = ($jump) * $rowTotal;
                    $this->logger?->log("Skipping from row $i to row $nextI, adding $jump x $rowTotal = $skippedRowTotal to total");
                    $total += $skippedRowTotal;
                    $i = $nextI;
                }
            }
        }

        return $total;
    }

    public function markVertexExcavated(Vertex $vertex): void {
        if (!$this->visualise) {
            return;
        }
        $point = $vertex->startPoint;
        $mapPoint = $this->getPointAt(...$point);
        $mapPoint->excavationDirection = $mapPoint->excavationDirection ?: $vertex->direction;
        $mapPoint->isExcavated = true;
        for ($i = 0; $i < $vertex->length; $i++) {
            $point = $vertex->direction->stepInDirection(...$point);
            $mapPoint = $this->getPointAt(...$point);
            $mapPoint->isExcavated = true;
            $mapPoint->excavationDirection = $vertex->direction;
        }
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

