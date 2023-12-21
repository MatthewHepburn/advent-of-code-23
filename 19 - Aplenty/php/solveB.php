<?php
declare(strict_types=1);

namespace AoC\Nineteen;

use AoC\Common\Logger;

require_once __DIR__ . '/common.php';
require_once __DIR__ . '/../../common/php/autoload.php';

class PartConstraint
{
    public int $minX = 1;
    public int $minM = 1;
    public int $minA = 1;
    public int $minS = 1;

    public int $maxX = 4000;
    public int $maxM = 4000;
    public int $maxA = 4000;
    public int $maxS = 4000;

    public function constrainTo(Rule $rule): void
    {
        if ($rule->condition === Condition::GT) {
            $this->setMin($rule->property, $rule->threshold + 1);
        } else {
            $this->setMax($rule->property, $rule->threshold - 1);
        }
    }

    private function setMin(Property $property, int $threshold): void
    {
        $propName = "min" . strtoupper($property->value);
        $this->{$propName} = max($threshold, $this->{$propName});
    }

    private function setMax(Property $property, int $threshold): void
    {
        $propName = "max" . strtoupper($property->value);
        $this->{$propName} = min($threshold, $this->{$propName});
    }

    public function getCombinations(): int
    {
        $total = 1;
        foreach (str_split('XMAS') as $property) {
            $min = $this->{"min{$property}"};
            $max = $this->{"max{$property}"};
            $total = $total * (1 + $max - $min);
        }

        return $total;
    }

    public function isSatisfiable(): bool
    {
        if ($this->minX > $this->maxX) {
            return false;
        }
        if ($this->minM > $this->maxM) {
            return false;
        }
        if ($this->minA > $this->maxA) {
            return false;
        }
        if ($this->minS > $this->maxS) {
            return false;
        }

        return true;
    }

    public function satisfies(Part $part): bool {
        if ($part->x < $this->minX || $part->x > $this->maxX) {
            return false;
        }
        if ($part->m < $this->minM || $part->m > $this->maxM) {
            return false;
        }
        if ($part->a < $this->minA || $part->a > $this->maxA) {
            return false;
        }
        if ($part->s < $this->minS || $part->s > $this->maxS) {
            return false;
        }

        return true;
    }

    public function __toString(): string
    {
        $combinations = $this->getCombinations();
        return "{$this->minX} <= x <= $this->maxX, {$this->minM} <= m <= $this->maxM, {$this->minA} <= a <= $this->maxA, {$this->minS} <= s <= $this->maxS => $combinations combinations";
    }
}

$logger = new Logger();

$problem = getProblem();
/** @var PathNode[][] $acceptedPaths */
$acceptedPaths = [];
/** @var PathNode[][] $incompletePaths */
$incompletePaths = [[new PathNode([], 'in')]];
while ($incompletePaths) {
    /** @var PathNode[][] $newIncompletePaths */
    $newIncompletePaths = [];
    foreach ($incompletePaths as $incompletePath) {
        $lastNode = $incompletePath[count($incompletePath) - 1];
        $workflow = $problem->workflows[$lastNode->target];
        $nextNodes = $workflow->getConnectingNodes();
        foreach ($nextNodes as $nextNode) {
            if ($nextNode->target === Terminal::Reject->value) {
                continue;
            }
            if ($nextNode->target === Terminal::Accept->value) {
                $acceptedPaths[]= array_merge($incompletePath, [$nextNode]);
            } else {
                $newIncompletePaths[]= array_merge($incompletePath, [$nextNode]);
            }
        }
    }
    $incompletePaths = $newIncompletePaths;
}

$logger->log("Found " . count($acceptedPaths) . " paths to acceptance");
$totalCombinations = 0;
/** @var PartConstraint[] $possibleConstraints */
$possibleConstraints = [];
foreach ($acceptedPaths as $acceptedPath) {
    $logger->log("Path: " . implode(' -> ', $acceptedPath));
    $partConstraint = new PartConstraint();
    foreach ($acceptedPath as $pathNode) {
        $rules = $pathNode->rulesToFulfill;
        foreach ($rules as $rule) {
            $partConstraint->constrainTo($rule);
        }
    }
    $logger->log("   Constraints: $partConstraint");
    if ($partConstraint->isSatisfiable()) {
        $totalCombinations += $partConstraint->getCombinations();
        $possibleConstraints []= $partConstraint;
    } else {
        $logger->log("     NOT SATISFIABLE!");
    }
}

// Logic check - repeat part A calculations:
$acceptedScore = 0;
foreach ($problem->parts as $part) {
    foreach ($possibleConstraints as $constraint) {
        if ($constraint->satisfies($part)) {
            $acceptedScore += $part->getScore();
            $logger->log("Accept $part under $constraint");
            break;
        }
    }
}

// 19114 for the example
$logger->log($acceptedScore . " for part A problem");

echo $totalCombinations . "\n";


