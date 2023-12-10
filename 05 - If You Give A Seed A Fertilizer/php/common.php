<?php
declare(strict_types=1);

namespace AoC\Five;

use AoC\Common\InputLoader;

require_once __DIR__ . '/../../common/php/autoload.php';

final readonly class RangeMapping {
    public function __construct(
        public int $destStart,
        public int $sourceStart,
        public int $length
    ) {}

    public function mapsToForwards(int $source): ?int {
        if ($source >= $this->sourceStart && $source < $this->sourceStart + $this->length) {
            // source is in our range:
            return $this->destStart + ($source - $this->sourceStart);
        }

        return null;
    }

    public function mapsToBackwards(int $dest): ?int {
        if ($dest >= $this->destStart && $dest < $this->destStart + $this->length) {
            // dest is in our range:
            return $this->sourceStart + ($dest - $this->destStart);
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

    public function mapsToForwards(int $source): int {
        foreach ($this->rangeMaps as $rangeMap) {
            $result = $rangeMap->mapsToForwards($source);
            if ($result !== null) {
                return $result;
            }
        }

        // No result, map to the source value
        return $source;
    }

    public function mapsToBackwards(int $dest): int {
        foreach ($this->rangeMaps as $rangeMap) {
            $result = $rangeMap->mapsToBackwards($dest);
            if ($result !== null) {
                return $result;
            }
        }

        // No result, map to the dest value
        return $dest;
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

final readonly class SeedRange
{
    public int $end;
    public function __construct(
        public int $start,
        public int $length
    )
    {
        $this->end = $this->start + $this->length;
    }

    public function inRange(int $seed): bool {
        return $seed >= $this->start && $seed < $this->end;
    }
}

function getSimpleSeeds(): array
{
    $seedsLine = (new InputLoader(__DIR__))->getAsStrings()[0];
    $seedsString = str_replace('seeds: ', '', $seedsLine);
    return array_map(fn(string $x) => (int) $x, explode(' ', $seedsString));
}

/**
 * @return SeedRange[]
 */
function getSeedRanges(): array
{
    $output = [];
    $input = getSimpleSeeds();
    foreach (array_chunk($input, 2) as [$start, $length]) {
        $output[] = new SeedRange($start, $length);
    }

    return $output;
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
