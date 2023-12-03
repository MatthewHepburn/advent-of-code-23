<?php
declare(strict_types=1);

namespace AoC\Three;

use Aoc\Common\AdjacenyGenerator2D;

final readonly class PartNumberInstance {
    public function __construct(
        public string $position,
        public int $partNumber
    ) {}
}

function extractNumber(array $schematic, AdjacenyGenerator2D $adjacenyGenerator, int $i, int $j): ?PartNumberInstance
{
    $thisChar = $schematic[$i][$j];
    if (!ctype_digit($thisChar)) {
        return null;
    }

    // Move to the leftmost digit in our number
    while (true) {
        $nextPos = $adjacenyGenerator->getLeft($i, $j);
        if (!$nextPos) {
            break;
        }
        $nextChar = $schematic[$nextPos[0]][$nextPos[1]];
        if (!ctype_digit($nextChar)) {
            break;
        }
        [$i, $j] = $nextPos;
    }

    // Record our start point to uniquely identify this instance of this part number
    $startPos = "$i,$j";

    // Now read our digits left to right
    $digits = [];
    while (true) {
        $char = $schematic[$i][$j];
        if (!ctype_digit($char)) {
            break;
        }
        $digits[]= $char;
        $nextPos = $adjacenyGenerator->getRight($i, $j);
        if (!$nextPos) {
            break;
        }
        [$i, $j] = $nextPos;
    }

    $digitString = implode('', $digits);
    return new PartNumberInstance($startPos, (int) $digitString);
}
