<?php
declare(strict_types=1);

namespace AoC\Thirteen;

use AoC\Common\InputLoader;
use AoC\Common\Logger;

require_once __DIR__ . '/../../common/php/autoload.php';

final class MirrorMap
{
    public ?Logger $logger = null;

    /**
     * @param string[][] $points
     */
    public function __construct(
        public array $points
    ) {}

    /**
     * @return int[]
     */
    public function findReflectionColumns(): array
    {
        $reflectionColumns = [];
        for ($i = 0; $i < count($this->points[0]) - 1; $i++) {
            // Do we have a reflection around $i?
            for ($offset = 0; $i + $offset + 1 < count($this->points[0]) && $i - $offset >= 0; $offset++) {
                for($j = 0; $j < count($this->points); $j++) {
                    if ($this->points[$j][$i - $offset] !== $this->points[$j][$i + 1 + $offset]) {
                        continue 3;
                    }
                }
            }
            $reflectionColumns[]= $i + 1;
        }

        return $reflectionColumns;
    }

    /**
     * @return int[]
     */
    public function findReflectionRows(): array
    {
        $reflectionRows = [];
        for ($i = 0; $i < count($this->points) - 1; $i++) {
            // Do we have a reflection around $i?
            for ($offset = 0; $i + $offset + 1 < count($this->points) && $i - $offset >= 0; $offset++) {
                $topRow = $this->points[$i - $offset];
                $bottomRow = $this->points[$i + 1 + $offset];

                for($j = 0; $j < count($topRow); $j++) {
                    if ($topRow[$j] !== $bottomRow[$j]) {
                        continue 3;
                    }
                }
            }
            $reflectionRows[]= $i + 1;
        }

        return $reflectionRows;
    }

    public function getDiagram(): string
    {
        $output = '';
        foreach ($this->points as $row) {
            $output .= implode('', $row) . "\n";
        }

        return $output;
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

function getMirrorMaps(): \Generator
{
    $input = (new InputLoader(__DIR__))->getAsString();
    $lines = explode(PHP_EOL, $input);

    $buffer = [];
    foreach ($lines as $line) {
        if ($line !== '') {
            $buffer[]= $line;
        } else {
            yield MirrorMap::fromLines($buffer);
            $buffer = [];
        }
    }
}

