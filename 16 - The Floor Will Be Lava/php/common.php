<?php
declare(strict_types=1);

namespace AoC\Sixteen;

use AoC\Common\AdjacenyGenerator2D;
use AoC\Common\InputLoader;

require_once __DIR__ . '/../../common/php/autoload.php';

final class MapPoint
{
    public bool $emittingNorth = false;
    public bool $emittingEast = false;
    public bool $emittingSouth = false;
    public bool $emittingWest = false;

    public function __construct(public readonly string $symbol) { }

    public function emitNorth(): bool
    {
        $changed = !$this->emittingNorth;
        $this->emittingNorth = true;
        return $changed;
    }
    public function emitEast(): bool
    {
        $changed = !$this->emittingEast;
        $this->emittingEast = true;
        return $changed;
    }
    public function emitSouth(): bool
    {
        $changed = !$this->emittingSouth;
        $this->emittingSouth = true;
        return $changed;
    }
    public function emitWest(): bool
    {
        $changed = !$this->emittingWest;
        $this->emittingWest = true;
        return $changed;
    }

    public function isEnergised(): bool
    {
        return $this->emittingNorth || $this->emittingEast || $this->emittingSouth || $this->emittingWest;
    }

    public function __toString(): string {
        if ($this->symbol !=='.') {
            return $this->symbol;
        }
        $count = array_sum([
            $this->emittingNorth ? 1 : 0,
            $this->emittingEast ? 1 : 0,
            $this->emittingSouth ? 1 : 0,
            $this->emittingWest ? 1: 0
        ]);
        if ($count === 0) {
            return $this->symbol;
        }
        if ($count > 1) {
            return (string) $count;
        }
        if ($this->emittingNorth) {
            return '^';
        }
        if ($this->emittingEast) {
            return '>';
        }
        if ($this->emittingSouth) {
            return 'v';
        }
        if ($this->emittingWest) {
            return '<';
        }

        throw new \Exception('Bad logic!');
    }
}

final class ContraptionMap
{
    /** @var MapPoint[][] */
    private array $points;
    private AdjacenyGenerator2D $adjacenyGenerator;

    /**
     * @param string[][] $points
     */
    public function __construct(
        array $points
    ) {
        $this->points = [];
        foreach ($points as $inputRow) {
            $this->points[]= array_map(fn(string $x) => new MapPoint($x), $inputRow);
        }

        $this->adjacenyGenerator = new AdjacenyGenerator2D(0, 0, count($this->points) - 1, count($this->points[0]) - 1, false);
    }

    public function energiseFromSouth(int $i, int $j): bool
    {
        $changed = false;
        $endPoint = $this->points[$i][$j];
        switch ($endPoint->symbol) {
            case '.':
            case '|':
                $changed = $endPoint->emitNorth();
                break;
            case '-':
                $changed = $endPoint->emitEast();
                $changed = $endPoint->emitWest() || $changed;
                break;
            case '\\':
                $changed = $endPoint->emitWest();
                break;
            case '/':
                $changed = $endPoint->emitEast();
                break;
        }

        return $changed;
    }

    public function energiseFromNorth(int $i, int $j): bool
    {
        $changed = false;
        $endPoint = $this->points[$i][$j];
        switch ($endPoint->symbol) {
            case '.':
            case '|':
                $changed = $endPoint->emitSouth();
                break;
            case '-':
                $changed = $endPoint->emitEast();
                $changed = $endPoint->emitWest() || $changed;
                break;
            case '\\':
                $changed = $endPoint->emitEast();
                break;
            case '/':
                $changed = $endPoint->emitWest();
                break;
        }

        return $changed;
    }

    public function energiseFromEast(int $i, int $j): bool
    {
        $changed = false;
        $endPoint = $this->points[$i][$j];
        switch ($endPoint->symbol) {
            case '.':
            case '-':
                $changed = $endPoint->emitWest();
                break;
            case '|':
                $changed = $endPoint->emitNorth();
                $changed = $endPoint->emitSouth() || $changed;
                break;
            case '\\':
                $changed = $endPoint->emitNorth();
                break;
            case '/':
                $changed = $endPoint->emitSouth();
                break;
        }

        return $changed;
    }

    public function energiseFromWest(int $i, int $j): bool
    {
        $changed = false;
        $endPoint = $this->points[$i][$j];
        switch ($endPoint->symbol) {
            case '.':
            case '-':
                $changed = $endPoint->emitEast();
                break;
            case '|':
                $changed = $endPoint->emitNorth();
                $changed = $endPoint->emitSouth() || $changed;
                break;
            case '\\':
                $changed = $endPoint->emitSouth();
                break;
            case '/':
                $changed = $endPoint->emitNorth();
                break;
        }

        return $changed;
    }

    public function getDiagram(): string
    {
        $output = '';
        foreach ($this->points as $row) {
            $output .= implode('', $row) . "\n";
        }

        return $output;
    }

    public function getEnergyDiagram(): string
    {
        $output = '';
        foreach ($this->points as $row) {
            foreach ($row as $point) {
                $output .= $point->isEnergised() ? '#' : ' ';
            }
            $output .= "\n";
        }

        return $output;
    }

    public function getEnergisedCount(): int
    {
        $total = 0;
        foreach ($this->points as $row) {
            foreach ($row as $mapPoint) {
                if ($mapPoint->isEnergised()) {
                    $total += 1;
                }
            }
        }

        return $total;
    }

    public function run(): void
    {
        do {
            $changed = $this->tick();
        } while ($changed);
    }

    public function tick(): bool
    {
        $changed = false;
        foreach ($this->adjacenyGenerator->getIs() as $i) {
            foreach ($this->adjacenyGenerator->getJs() as $j) {
                $startPoint = $this->points[$i][$j];

                if ($startPoint->emittingNorth && $this->adjacenyGenerator->getUp($i, $j)) {
                    [$endI, $endJ] = $this->adjacenyGenerator->getUp($i, $j);
                    $changed = $this->energiseFromSouth($endI, $endJ) || $changed;
                }
                if ($startPoint->emittingSouth && $this->adjacenyGenerator->getDown($i, $j)) {
                    [$endI, $endJ] = $this->adjacenyGenerator->getDown($i, $j);
                    $changed = $this->energiseFromNorth($endI, $endJ) || $changed;
                }
                if ($startPoint->emittingEast && $this->adjacenyGenerator->getRight($i, $j)) {
                    [$endI, $endJ] = $this->adjacenyGenerator->getRight($i, $j);
                    $changed = $this->energiseFromWest($endI, $endJ) || $changed;
                }
                if ($startPoint->emittingWest && $this->adjacenyGenerator->getLeft($i, $j)) {
                    [$endI, $endJ] = $this->adjacenyGenerator->getLeft($i, $j);
                    $changed = $this->energiseFromEast($endI, $endJ) || $changed;
                }
            }
        }
        return $changed;
    }
}

function getContraptionMap(): ContraptionMap
{
    $input = (new InputLoader(__DIR__))->getAsCharArray();
    return new ContraptionMap($input);
}

