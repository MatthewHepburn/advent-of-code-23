<?php
declare(strict_types=1);

namespace AoC\Ten;

use AoC\Common\Direction2D;
use AoC\Common\InputLoader;
use AoC\Common\Point;

require_once __DIR__ . '/../../common/php/InputLoader.php';
require_once __DIR__ . '/../../common/php/Point.php';

final readonly class Pipe
{
    /** @var Point[] */
    private array $acceptedPoints;
    public bool $isStart;
    public function __construct(private Point $position, private string $symbol) {
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
final readonly class Maze
{
    /**
     * @param Pipe[][] $pipes
     * @param Pipe $startPos
     */
    public function __construct(
        public array $pipes,
        public Pipe $startPos
    ) {}
}

function getSequences(): array
{
    return (new InputLoader(__DIR__))->getAsIntArrays();
}
