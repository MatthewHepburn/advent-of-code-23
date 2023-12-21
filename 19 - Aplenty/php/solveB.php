<?php
declare(strict_types=1);

namespace AoC\Nineteen;

use AoC\Common\Logger;

require_once __DIR__ . '/common.php';
require_once __DIR__ . '/../../common/php/autoload.php';

class PartConstraint
{
    public $minX = 1;
    public $minM = 1;
    public $minA = 1;
    public $minS = 1;

    public $maxX = 4000;
    public $maxM = 4000;
    public $maxA = 4000;
    public $maxS = 4000;

    public function constrainTo(Rule $rule): void
    {
        if ($rule->condition === Condition::GT) {
            $this->setMin($rule->property, $rule->threshold);
        } else {
            $this->setMax($rule->property, $rule->threshold);
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
        if ($this->minS > $this->maxX) {
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
/** @var Workflow[][] $acceptedPaths */
$acceptedPaths = [];
/** @var Workflow[][] $incompletePaths */
$incompletePaths = [[$problem->workflows['in']]];
while ($incompletePaths) {
    $newIncompletePaths = [];
    foreach ($incompletePaths as $incompletePath) {
        $targets = $incompletePath[count($incompletePath) - 1]->getTargets();
        foreach ($targets as $target) {
            if ($target === Terminal::Reject->value) {
                continue;
            }
            if ($target === Terminal::Accept->value) {
                $acceptedPaths[]= $incompletePath;
            } else {
                $newIncompletePaths[]= array_merge($incompletePath, [$problem->workflows[$target]]);
            }
        }
    }
    $incompletePaths = $newIncompletePaths;
}

$logger->log("Found " . count($acceptedPaths) . " paths to acceptance");
$totalCombinations = 0;
foreach ($acceptedPaths as $acceptedPath) {
    $logger->log("Path: " . implode(' -> ', $acceptedPath));
    $partConstraint = new PartConstraint();
    foreach ($acceptedPath as $i => $workflow) {
        if (isset($acceptedPath[$i + 1])) {
            $target = $acceptedPath[$i + 1]->label;
        } else {
            // We're at the last node, so target an acceptance
            $target = Terminal::Accept->value;
        }
        $rules = $workflow->getRulesForTarget($target);
        foreach ($rules as $rule) {
            $partConstraint->constrainTo($rule);
        }
    }
    $logger->log("   Constraints: $partConstraint");
    if ($partConstraint->isSatisfiable()) {
        $totalCombinations += $partConstraint->getCombinations();
    } else {
        $logger->log("     NOT SATISFIABLE!");
    }
}

echo $totalCombinations . "\n";


/**
 * TODO: Handle case like
 * Path: in{s<1351:px,qqz} -> qqz{s>2770:qs,m<1801:hdj,R} -> qs{s>3448:A,lnx} -> lnx{m>1548:A,A}
 * Constraints: 1 <= x <= 4000, 1548 <= m <= 4000, 1 <= a <= 4000, 2770 <= s <= 3449 => 26625210400908 combinations
 *
 * where a workflow has multiple paths to a target
 */
