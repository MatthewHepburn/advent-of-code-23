<?php
declare(strict_types=1);

namespace AoC\Fourteen;

use AoC\Common\InputLoader;
use function AoC\Common\filter;

require_once __DIR__ . '/../../common/php/autoload.php';

final class DishMap
{

    /**
     * @param string[][] $points
     */
    public function __construct(
        public array $points
    ) {}

    public function getDiagram(): string
    {
        $output = '';
        foreach ($this->points as $row) {
            $output .= implode('', $row) . "\n";
        }

        return $output;
    }

    public function getNorthLoad()
    {
        $total = 0;
        $rowValue = count($this->points);
        foreach ($this->points as $row) {
            $total += count(filter($row, fn(string $x) => $x === 'O')) * $rowValue;
            $rowValue = $rowValue - 1;
        }

        return $total;
    }

    public function tiltNorth(): void
    {
        $changed = true;
        while ($changed) {
            $changed = false;
            for ($row = 1; $row < count($this->points); $row++) {
                for ($column = 0; $column < count($this->points[0]); $column++) {
                    if ($this->points[$row - 1][$column] === '.' && $this->points[$row][$column] === 'O') {
                        $changed = true;
                        $this->points[$row - 1][$column] = 'O';
                        $this->points[$row][$column] = '.';
                    }
                }

            }
        }
    }

    /**
     * @param string[] $lines
     *
     * @return self
     */
    public static function fromLines(array $lines): self
    {
        $points = [];
        foreach ($lines as $line) {
            $points[]= str_split($line);
        }

        return new self($points);
    }
}

function getDishMap(): DishMap
{
    $input = (new InputLoader(__DIR__))->getAsCharArray();
    return new DishMap($input);
}

