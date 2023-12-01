<?php

class InputLoader
{
    public function __construct(private string $dir) {}

    public function getAsString() : string
    {
        $filename = getenv('AOC_EXAMPLE_MODE') ? 'exampleInput.txt' : 'input.txt';
        return file_get_contents($this->dir . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . $filename);
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
