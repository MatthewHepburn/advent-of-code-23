<?php

namespace Aoc\Common;

require_once __DIR__ . '/StandardLib.php';

readonly class AdjacenyGenerator2D
{
    public function __construct(
        private int $minI,
        private int $minJ,
        private int $maxI,
        private int $maxJ,
        private bool $withDiagonals
    ) {}

    public function getAdjacent(int $i, int $j): array {
        $candidates = [
            $this->getUp($i, $j),
            $this->getLeft($i, $j),
            $this->getRight($i, $j),
            $this->getDown($i, $j)
        ];
        if ($this->withDiagonals) {
            $candidates[]= [$i - 1, $j - 1];
            $candidates[]= [$i - 1, $j + 1];
            $candidates[]= [$i + 1, $j - 1];
            $candidates[]= [$i + 1, $j + 1];
        }

        $candidates = filter($candidates, fn($x) => is_array($x));
        return filter($candidates, fn(array $candidate) => $this->isInBounds($candidate));
    }

    public function getUp(int $i, int $j): ?array {
        $candidate = [$i - 1, $j];
        return $this->isInBounds($candidate) ? $candidate : null;
    }

    public function getDown(int $i, int $j): ?array {
        $candidate = [$i + 1, $j];
        return $this->isInBounds($candidate) ? $candidate : null;
    }

    public function getLeft(int $i, int $j): ?array {
        $candidate = [$i, $j - 1];
        return $this->isInBounds($candidate) ? $candidate : null;
    }

    public function getRight(int $i, int $j): ?array {
        $candidate = [$i, $j + 1];
        return $this->isInBounds($candidate) ? $candidate : null;
    }

    private function isInBounds(array $candidate): bool {
        [$i, $j] = $candidate;
        if ($i < $this->minI || $i > $this->maxI) {
            return false;
        }
        if ($j < $this->minJ || $j > $this->maxJ) {
            return false;
        }

        return true;
    }
}
