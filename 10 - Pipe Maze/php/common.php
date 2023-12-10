<?php
declare(strict_types=1);

namespace AoC\Ten;

use AoC\Common\Direction2D;
use AoC\Common\InputLoader;
use AoC\Common\Point;
use AoC\Common\Search\MapInterface;
use AoC\Common\Search\NodeInterface;
use AoC\Common\Search\NullCost;
use AoC\Common\Search\RouteInterface;
use AoC\Common\Search\ScalarCost;
use AoC\Common\Search\Vertex;
use function AoC\Common\filter;

require_once __DIR__ . '/../../common/php/autoload.php';

final class PipeData
{
    public ?int $minDistanceFromStart = null;
    public ?RouteInterface $bestRoute = null;
    public bool $enclosed = true;

    public function recordNewRoute(int $distance, RouteInterface $route): bool
    {
        if ($this->minDistanceFromStart === null) {
            $this->minDistanceFromStart = $distance;
            $this->bestRoute = $route;
            return true;
        }
        $newValue = min($distance, $this->minDistanceFromStart);
        if ($newValue < $this->minDistanceFromStart) {
            $this->minDistanceFromStart = $newValue;
            $this->bestRoute = $route;
            return true;
        }
        return false;
    }
}

final readonly class Pipe implements NodeInterface
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

    public function getEnclosureSymbol()
    {
        if (!$this->isGround()) {
            return $this->symbol;
        }

        return $this->pipeData->enclosed ? 'I' : 'O';
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

    public function isPartOfLoop(): bool
    {
        return ! $this->isGround() && $this->pipeData->minDistanceFromStart !== null;
    }

    public function isImprovement(RouteInterface $route): bool
    {
        $cost = $route->getTotalCost();
        if ($cost instanceof NullCost) {
            $value = 0;
        } else if ($cost instanceof ScalarCost) {
            $value = $cost->value;
        } else {
            throw new \Exception('Encountered unexpected Cost class');
        }

        return $this->pipeData->recordNewRoute($value, $route);
    }

    public function getBestRoute(): ?RouteInterface
    {
        return $this->pipeData->bestRoute;
    }
}

final readonly class PipeMaze implements MapInterface
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

    public function getEnclosureDiagram(): string
    {
        $output = '';
        foreach ($this->pipes as $row) {
            $distances = array_map(fn(Pipe $p) => $p->getEnclosureSymbol(), $row);
            $output .= implode('', $distances) . "\n";
        }

        return $output;
    }

    public function getVerticesFrom(NodeInterface $node): array
    {
        if (!$node instanceof Pipe) {
            throw new \Exception('Encountered unexpected Node class');
        }

        $nodes = $this->getConnectedPipes($node);
        $unitCost = new ScalarCost(1);
        return array_map(fn(Pipe $pipe) => new Vertex($node, $pipe, $unitCost), $nodes);
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
