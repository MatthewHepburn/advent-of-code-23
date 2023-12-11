<?php
declare(strict_types=1);

namespace AoC\Eleven;

use AoC\Common\InputLoader;
use AoC\Common\Point;

require_once __DIR__ . '/../../common/php/autoload.php';


final class StarMap {
    private int $expansionConstant = 2;

    /**
     * @param string[][] $map
     */
    public function __construct(
        private array $map
    ) {}

    public function expand(): void {
        // First, expand our rows
        $newMap = [];
        foreach ($this->map as $row) {
            $isEmpty = true;
            foreach ($row as $point) {
                if ($point !== '.') {
                    $isEmpty = false;
                    break;
                }
            }
            if ($isEmpty) {
                for ($i = 0; $i < $this->expansionConstant; $i++) {
                    $newMap[]= $row;
                }
            } else {
                // Just copy our row to the new map once
                $newMap[]= $row;
            }
        }
        $this->map = $newMap;

        // Now, expand our columns
        // First, a pass to identify columns to be expanded
        $emptyColumns = [];
        for ($columnNumber = 0; $columnNumber < count($this->map[0]); $columnNumber++) {
            $isEmpty = true;
            for ($rowNumber = 0; $rowNumber < count($this->map); $rowNumber++) {
                if ($this->map[$rowNumber][$columnNumber] !== '.') {
                    $isEmpty = false;
                    break;
                }
            }
            if ($isEmpty) {
                $emptyColumns[$columnNumber]= true;
            }
        }

        // Then a pass to do the actual expansion
        $newMap = [];
        foreach ($this->map as $row) {
            $newRow = [];
            foreach ($row as $columnNumber => $point) {
                if (isset($emptyColumns[$columnNumber])) {
                    // Expand!
                    for ($i = 0; $i < $this->expansionConstant; $i++) {
                        $newRow[]= $point;
                    }
                } else {
                    // Just copy
                    $newRow[]= $point;
                }
            }
            $newMap[]= $newRow;
        }

        $this->map = $newMap;
    }

    /**
     * @return Point[]
     */
    public function getGalaxies(): array
    {
        $galaxies = [];
        $galaxyNumber = 1;
        for ($y = 0; $y < count($this->map); $y++) {
            for ($x = 0; $x < count($this->map[0]); $x++) {
                if ($this->map[$y][$x] !== '.') {
                    $galaxies[$galaxyNumber] = new Point($y, $x);
                    $galaxyNumber++;
                }
            }
        }

        return $galaxies;
    }

    public function getStarDiagram(): string
    {
        $output = '';
        foreach ($this->map as $row) {
            $output .= implode('', $row) . "\n";
        }

        return $output;
    }
}

function getStarMap(): StarMap
{
    $chars = (new InputLoader(__DIR__))->getAsCharArray();

    return new StarMap($chars);
}
