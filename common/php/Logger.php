<?php

class Logger
{
    private bool $silentMode = true;

    public function __construct()
    {
        if (getenv('AOC_DEBUG')) {
            $this->silentMode = false;
        }
    }
    public function log (string $message)
    {
        if (!$this->silentMode) {
            echo $message . PHP_EOL;
        }
    }
}
