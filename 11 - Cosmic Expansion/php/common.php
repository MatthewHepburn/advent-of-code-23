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
        $emptyRows = $this->getEmptyRows();
        $newMap = [];
        foreach ($this->map as $rowNumber => $row) {
            $isEmpty = isset($emptyRows[$rowNumber]);
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
        $emptyColumns = $this->getEmptyColumns();
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

    public function getEmptyRows(): array
    {
        $rows = [];
        foreach ($this->map as $rowNumber => $row) {
            $isEmpty = true;
            foreach ($row as $point) {
                if ($point !== '.') {
                    $isEmpty = false;
                    break;
                }
            }
            if ($isEmpty) {
                $rows[$rowNumber] = true;
            }
        }

        return $rows;
    }

    public function getEmptyColumns(): array
    {
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
        return $emptyColumns;
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
