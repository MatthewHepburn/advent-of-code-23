<?php
declare(strict_types=1);

namespace AoC\Five;

use AoC\Common\InputLoader;

require_once __DIR__ . '/../../common/php/InputLoader.php';

final readonly class RangeMapping {
    public function __construct(
        public int $destStart,
        public int $sourceStart,
        public int $length
    ) {}

    public function mapsTo(int $source): ?int {
        if ($source >= $this->sourceStart && $source < $this->sourceStart + $this->length) {
            // source is in our range:
            return $this->destStart + ($source - $this->sourceStart);
        }

        return null;
    }

    public static function fromLine(string $x): self
    {
        $parts = explode(' ', $x);
        $ints = array_map(fn(string $x) => (int) $x, $parts);
        return new RangeMapping(...$ints);
    }
}

final readonly class Map {
    /**
     * @param string $from
     * @param string $to
     * @param RangeMapping[] $rangeMaps
     */
    public function __construct(
        public string $from,
        public string $to,
        public array $rangeMaps
    ) {}

    public function mapsTo(int $source): int {
        foreach ($this->rangeMaps as $rangeMap) {
            $result = $rangeMap->mapsTo($source);
            if ($result !== null) {
                return $result;
            }
        }

        // No result, map to the source value
        return $source;
    }

    public static function fromInput(string $inputPart): self
    {
        $lines = explode("\n", $inputPart);
        $titleLine = $lines[0];
        unset($lines[0]);
        [$title, $discard] = explode(' ', $titleLine);
        [$from, $to] = explode('-to-', $title);

        $lines = array_filter($lines, fn(string $x) => $x !== '');
        $mappings = array_map(fn(string $x) => RangeMapping::fromLine($x), $lines);
        return new self($from, $to, $mappings);
    }
}

function getSeeds(): array
{
    $seedsLine = (new InputLoader(__DIR__))->getAsStrings()[0];
    $seedsString = str_replace('seeds: ', '', $seedsLine);
    return array_map(fn(string $x) => (int) $x, explode(' ', $seedsString));
}

/**
 * @return Map[]
 */
function getMaps(): array
{
    $inputString = (new InputLoader(__DIR__))->getAsString();
    $parts = explode("\n\n", $inputString);

    // Discard seeds line:
    unset($parts[0]);


    $maps = [];
    foreach ($parts as $inputPart) {
        $maps[]= Map::fromInput($inputPart);
    }

    return $maps;
}
