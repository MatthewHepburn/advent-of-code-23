<?php
declare(strict_types=1);

namespace AoC\Eighteen;

use AoC\Common\InputLoader;
use function AoC\Common\filter;

require_once __DIR__ . '/../../common/php/autoload.php';

enum Direction: string
{
    case Up = 'U';
    case Down = 'D';
    case Left = 'L';
    case Right = 'R';

    public function stepInDirection(int $i, int $j): array {
        return match ($this) {
            Direction::Up => [$i - 1, $j],
            Direction::Down => [$i + 1, $j],
            Direction::Left => [$i, $j - 1],
            Direction::Right => [$i, $j + 1],

        };
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

final class MapPoint
{
    public bool $isExcavated = false;
    public ?string $edgeColour = null;
    public ?Direction $digDirection = null;

    public function __toString(): string
    {
        return $this->digDirection ? $this->digDirection->value : ' ';
    }
}

final class ExcavationMap
{
    /** @var MapPoint[][] */
    public array $points;
    public readonly array $startPoint;

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
        foreach ($steps as $step) {
            for ($d = 0; $d < $step->distance; $d ++) {
                $position = $step->direction->stepInDirection(...$position);
                $point = $this->getPointAt(...$position);
                $point->isExcavated = true;
                $point->edgeColour = $step->edgeColour;
                $point->digDirection = $step->direction;
            }
        }
    }

    public function markInner(): int
    {
        $excavated = 0;
        foreach ($this->points as $i => $row) {
            $inTrench = false;
            $lastBoundary = null;
            foreach ($row as $j => $point) {
                $isExcavated = $point->isExcavated;
                if ($isExcavated) {
                    $isBoundary = $point->digDirection === Direction::Up || $point->digDirection === Direction::Down;
                    if ($isBoundary) {
                        if ($lastBoundary && $lastBoundary === $point->digDirection) {
                            $inTrench = !$inTrench;
                        } elseif ($lastBoundary === null) {
                            $inTrench = !$inTrench;
                        }
                        $lastBoundary = $point->digDirection;
                    }
                } else if ($inTrench && !$excavated) {
                    $lastBoundary = null;
                    $point->isExcavated = true;
                }

                if ($point->isExcavated) {
                    $excavated += 1;
                }
            }
        }

        return $excavated;
    }

    public function getDiagram(): string
    {
        $output = '';
        foreach ($this->points as $row) {
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

