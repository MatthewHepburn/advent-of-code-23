<?php

namespace AoC\Common;

class InputLoader
{
    public function __construct(private string $dir) {}

    public function getAsString() : string
    {
        $isExample = !! getenv('AOC_EXAMPLE_MODE');

        $filename = $isExample ? 'exampleInput.txt' : 'input.txt';
        $dir = $this->dir . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR;

        // Some problems have different example input for parts A and B:
        $standardFilePath = $dir . $filename;
        if (!$isExample || file_exists($standardFilePath)) {
            return file_get_contents($standardFilePath);
        } else {
            $callScript = ($_SERVER['SCRIPT_FILENAME']);
            $filename = match ($callScript) {
                'solveA.php' => 'exampleInputA.txt',
                'solveB.php' => 'exampleInputB.txt',
                default => throw new Exception('Unexpected name for call script')
            };
            return file_get_contents($dir . $filename);
        }
    }

    /**
     * @return string[]
     */
    public function getAsStrings() : array
    {
        return array_filter(explode(PHP_EOL, $this->getAsString()));
    }

    /**
     * @return int[]
     */
    public function getAsInts() : array
    {
        $output = [];
        foreach ($this->getAsStrings() as $string) {
            $output[]= (int) $string;
        }

        return $output;
    }

    /**
     * @return int[][]
     */
    public function getAsIntArrays() : array
    {
        $output = [];
        foreach ($this->getAsStrings() as $string) {
            $parts = explode(' ', $string);
            $output[]= array_map(fn(string $x) => (int) $x, $parts);
        }

        return $output;
    }

    /**
     * @return string[][]
     */
    public function getAsCharArray() : array
    {
        $output = [];
        foreach ($this->getAsStrings() as $string) {
            $line = [];
            foreach (str_split($string) as $char) {
                $line []= $char;
            }
            $output[]= $line;
        }

        return $output;
    }
}
