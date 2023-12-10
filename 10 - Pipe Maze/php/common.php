<?php
declare(strict_types=1);

namespace AoC\Ten;

use AoC\Common\Direction2D;
use AoC\Common\InputLoader;
use AoC\Common\Point;
use function AoC\Common\filter;

require_once __DIR__ . '/../../common/php/InputLoader.php';
require_once __DIR__ . '/../../common/php/Point.php';
require_once __DIR__ . '/../../common/php/Direction2D.php';
require_once __DIR__ . '/../../common/php/StandardLib.php';

final class PipeData
{
    public ?int $minDistanceFromStart = null;

    public function recordNewDistance(int $distance): bool
    {
        if ($this->minDistanceFromStart === null) {
            $this->minDistanceFromStart = $distance;
            return true;
        }
        $newValue = min($distance, $this->minDistanceFromStart);
        if ($newValue < $this->minDistanceFromStart) {
            $this->minDistanceFromStart = $newValue;
            return true;
        }
        return false;
    }
}

final readonly class Pipe
{
    /** @var Point[] */
    private array $acceptedPoints;
    public bool $isStart;
    public PipeData $pipeData;

    public function __construct(private Point $position, public string $symbol) {
        $this->pipeData = new PipeData();
        $acceptedDirections = match ($this->symbol){
            '.' => [], // The null Pipe,
            '|' => [Direction2D::Up, Direction2D::Down],
            '-' => [Direction2D::Left, Direction2D::Right],
            'L' => [Direction2D::Up, Direction2D::Right],
            'J' => [Direction2D::Up, Direction2D::Left],
            '7' => [Direction2D::Down, Direction2D::Left],
            'F' => [Direction2D::Down, Direction2D::Right],
            'S' => [Direction2D::Up, Direction2D::Left, Direction2D::Right, Direction2D::Down]
        };
        $this->acceptedPoints = array_map(fn(Direction2D $d) => $this->position->getInDirection($d), $acceptedDirections);
        $this->isStart = $this->symbol === 'S';
    }

    public function isGround(): bool
    {
        return $this->symbol === '.';
    }

    public function getPosition(): Point
    {
        return $this->position;
    }

    public function acceptsConnectionFrom(Point $point): bool
    {
        foreach ($this->acceptedPoints as $acceptedPoint) {
            if ($acceptedPoint->equals($point)) {
                return true;
            }
        }
        return false;
    }

    public function __toString(): string
    {
        return $this->symbol;
    }
}

enum TilePosition {
    case TopLeft;
    case TopRight;
    case Top;
    case Left;
    case Right;
    case Bottom;
    case BottomLeft;
    case BottomRight;
}

final class GroundData
{
    public function __construct(
        private bool $isOpenGround
    ) {

    }

    public array $minDistancesFromExit = [
        Direction2D::Up->name => null,
        Direction2D::Down->name => null,
        Direction2D::Right->name => null,
        Direction2D::Left->name => null,
    ];

    public function recordNewDistance(int $distance, Direction2D $side): bool
    {
        if ($this->minDistancesFromExit[$side->name] === null) {
            $this->minDistancesFromExit[$side->name] = $distance;
            return true;
        }
        $newValue = min($distance, $this->minDistancesFromExit[$side->name]);
        if ($newValue < $this->minDistancesFromExit[$side->name]) {
            $this->minDistancesFromExit[$side->name] = $newValue;
            return true;
        }
        return false;
    }

    public function isReachable(): bool
    {
        foreach ($this->minDistancesFromExit as $distance) {
            if ($distance !== null) {
                return true;
            }
        }

        return false;
    }
}

final readonly class GroundPosition
{
    public function __construct(
        public Ground $ground,
        public Direction2D $side,
    ) {}
}

final readonly class Ground
{
    public GroundData $groundData;
    public bool $isPipe;

    public function __construct(private Point $position, private string $symbol, public bool $isExit) {
        $this->groundData = new GroundData();

        $this->isPipe = $this->symbol !== '.';
    }

    public function getEnclosureSymbol()
    {
        if ($this->isPipe) {
            return $this->symbol;
        }
        if ($this->isEnclosed()) {
            return 'I';
        }
        return 'O';
    }

    public function isEnclosed(): bool
    {
        return !$this->isPipe && $this->groundData->minDistanceFromExit === null;
    }

    public function getPosition(): Point
    {
        return $this->position;
    }

    public function acceptsConnectionFrom(Point $point): bool
    {
        foreach ($this->acceptedPoints as $acceptedPoint) {
            if ($acceptedPoint->equals($point)) {
                return true;
            }
        }
        return false;
    }

    public function __toString(): string
    {
        return $this->symbol;
    }
}
final readonly class PipeMaze
{
    /**
     * @param Pipe[][] $pipes
     * @param Pipe $startPipe
     */
    public function __construct(
        public array $pipes,
        public Pipe $startPipe
    ) {}

    /**
     * @param Pipe $pipe
     *
     * @return Pipe[];
     */
    public function getConnectedPipes(Pipe $pipe): array
    {
        $startPos = $pipe->getPosition();
        $possibleDirections = Direction2D::cases();
        $possiblePositions = array_map(fn(Direction2D $d) => $startPos->getInDirection($d), $possibleDirections);
        $inBoundsPositions = filter($possiblePositions, fn(Point $p) => $this->isInBounds($p));
        $connectablePositions = filter($inBoundsPositions, fn(Point $p) => $pipe->acceptsConnectionFrom($p));
        $candidatePipes = array_map(fn(Point $p) => $this->pipes[$p->y][$p->x], $connectablePositions);
        return filter($candidatePipes, fn(Pipe $candidate) => $candidate->acceptsConnectionFrom($startPos));
    }

    public function isInBounds(Point $point): bool {
        if ($point->x < 0 || $point->y < 0) {
            return false;
        }
        if ($point->x >= count($this->pipes[0])) {
            return false;
        }

        return $point->y < count($this->pipes);
    }

    public function isEdge(Point $point): bool {
        if ($point->x === 0 || $point->y === 0) {
            return true;
        }

        if ($point->y === count($this->pipes) - 1) {
            return true;
        }
        if ($point->x === count($this->pipes[0]) - 1) {
            return true;
        }

        return false;
    }

    public function getPipeDiagram(): string
    {
        $output = '';
        foreach ($this->pipes as $row) {
            $output .= implode('', $row) . "\n";
        }

        return $output;
    }

    public function getDistanceDiagram(): string
    {
        $output = '';
        foreach ($this->pipes as $row) {
            $distances = array_map(fn(Pipe $p) => $p->pipeData->minDistanceFromStart ?? '.', $row);
            $output .= implode('', $distances) . "\n";
        }

        return $output;
    }
}

final readonly class GroundMaze
{
    /**
     * @param Ground[][] $ground
     */
    public function __construct(
        public array $ground
    ) {}

    /**
     * @param Ground $pipe
     *
     * @return Ground[]
     */
    public function getConnectedGround(Ground $ground): array
    {
        $startPos = $ground->getPosition();
        $possibleDirections = Direction2D::cases();
        $possiblePositions = array_map(fn(Direction2D $d) => $startPos->getInDirection($d), $possibleDirections);
        $inBoundsPositions = filter($possiblePositions, fn(Point $p) => $this->isInBounds($p));
        echo "Starting from $startPos, these are in bounds: " . implode(' ', $inBoundsPositions) . "\n";
        $connectablePositions = filter($inBoundsPositions, fn(Point $p) => $ground->acceptsConnectionFrom($p));
        $candidateGround = array_map(fn(Point $p) => $this->ground[$p->y][$p->x], $connectablePositions);
        return filter($candidateGround, fn(Ground $candidate) => $candidate->acceptsConnectionFrom($startPos));
    }

    public function isInBounds(Point $point): bool {
        if ($point->x < 0 || $point->y < 0) {
            return false;
        }
        if ($point->x >= count($this->ground[0])) {
            return false;
        }

        if ($point->y >= count(($this->ground))) {
            return false;
        }

        return true;
    }

    public function getEnclosureDiagram(): string
    {
        $output = '';
        foreach ($this->ground as $row) {
            $markings = array_map(fn(Ground $g) => $g->getEnclosureSymbol(), $row);
            $output .= implode('', $markings) . "\n";
        }

        return $output;
    }
}


function getMaze(): PipeMaze
{
    $chars = (new InputLoader(__DIR__))->getAsCharArray();

    $pipes = [];
    $startPipe = null;
    for ($y = 0; $y < count($chars); $y++) {
        $pipeRow = [];
        for ($x = 0; $x < count($chars[$y]); $x++) {
            $pipe = new Pipe(new Point(y: $y, x: $x), $chars[$y][$x]);
            $pipeRow[]= $pipe;
            $startPipe = $pipe->isStart ? $pipe : $startPipe;
        }
        $pipes[]= $pipeRow;
    }

    return new PipeMaze($pipes, $startPipe);
}
