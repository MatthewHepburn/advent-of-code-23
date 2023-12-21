<?php
declare(strict_types=1);

namespace AoC\Nineteen;

use AoC\Common\InputLoader;
use function AoC\Common\filter;

require_once __DIR__ . '/../../common/php/autoload.php';

enum Property: string {
    case X = 'x';
    case M = 'm';
    case A = 'a';
    case S = 's';
}

enum Terminal: string {
    case Accept = 'A';
    case Reject = 'R';
}

enum Condition: string {
    case GT = '>';
    case LT = '<';
}
final readonly class Rule
{
    public function __construct(
        public Property $property,
        public Condition $condition,
        public int $threshold,
        public string $target
    ){}

    public function evaluateFor(Part $part): ?string
    {
        $value = $part->getFor($this->property);
        $result = match ($this->condition) {
            Condition::GT => $value > $this->threshold,
            Condition::LT => $value < $this->threshold
        };
        return $result ? $this->target : null;
    }

    public static function fromString(string $s)
    {
        $property = Property::from($s[0]);
        $condition = Condition::from($s[1]);
        [$discard, $threshold, $target] = preg_split('/[><:]/', $s);

        return new self($property, $condition, (int) $threshold, $target);
    }

    public function negate(): self
    {
        $condition = match ($this->condition) {
            Condition::GT => Condition::LT,
            Condition::LT => Condition::GT,
        };
        // Adjust threshold by one since we're avoiding GTE / LTE
        if ($this->condition === Condition::LT) {
            $threshold = $this->threshold - 1;
        } else {
            $threshold = $this->threshold + 1;
        }

        return new self($this->property, $condition, $threshold, '?');
    }

    public function __toString(): string
    {
        return "({$this->property->value} {$this->condition->value} {$this->threshold}: $this->target)";
    }
}
final readonly class Workflow
{
    /**
     * @param string $label
     * @param Rule[] $rules
     * @param string $defaultTarget
     */
    public function __construct(public string $label, private array $rules, private string $defaultTarget, private string $line) {}

    public function evaluateFor(Part $part): string
    {
        foreach ($this->rules as $rule) {
            $target = $rule->evaluateFor($part);
            if ($target) {
                return $target;
            }
        }

        return $this->defaultTarget;
    }

    public static function fromLine(string $line): self
    {
        [$label, $rem] = explode('{', $line);
        $parts = explode(',', trim($rem, '}'));
        $default = array_pop($parts);
        $rules = array_map(fn(string $s) => Rule::fromString($s), $parts);

        return new self($label, $rules, $default, $line);
    }

    /**
     * @return PathNode[]
     */
    public function getConnectingNodes(): array
    {
        $output = [];
        for ($i = 0; $i <= count($this->rules); $i++) {
            $rules = [];
            for ($j = 0; $j < $i; $j++) {
                $rules[]= $this->rules[$j]->negate();
            }
            $rules[]=$this->rules[$i] ?? $this->rules[$i -1]->negate();
            $lastRuleTarget = $rules[count($rules) -1]->target;
            $target = $lastRuleTarget === '?' ? $this->defaultTarget : $lastRuleTarget;
            $output[]= new PathNode($rules, $target);
        }

        // Check our logic:
        if (count($output) !== count($this->rules) + 1) {
            throw new \Exception('Bad logic!');
        }

        return $output;
    }

    /**
     * @param string $target
     *
     * @return Rule[];
     */
    public function getRulesForTarget(string $target): array
    {
        $rules = [];
        foreach ($this->rules as $rule) {
            if ($rule->target === $target) {
                // Easy, we need this rule to hold
                $rules[]= $rule;
                return $rules;
            }
            // Otherwise, we need it not to hold so that we get to the next rule
            $rules[]= $rule->negate();
        }

        // All rules exhausted, lets check our logic:
        if ($target !== $this->defaultTarget) {
            throw new \Exception("Could not find rules for target $target in workflow {$this}");
        }

        return $rules;
    }

    /**
     * @return string[]
     */
    public function getTargets(): array
    {
        $output = [];
        foreach ($this->rules as $rule) {
            $output[$rule->target] = true;
        }
        $output[$this->defaultTarget] = true;

        return array_keys($output);
    }

    public function __toString(): string
    {
        return $this->line;
    }
}

final readonly class Part
{
    public function __construct(
        public int $x,
        public int $m,
        public int $a,
        public int $s
    ) {}

    public function getFor(Property $property): int
    {
        return $this->{$property->value};
    }

    public static function fromLine(string $line): self {
        [$xStr, $mStr, $aStr, $sStr] = explode(',', $line);
        [$discard, $x] = explode('=', $xStr);
        [$discard, $m] = explode('=', $mStr);
        [$discard, $a] = explode('=', $aStr);
        [$discard, $s] = explode('=', $sStr);

        return new self(
            (int)$x, (int)$m, (int)$a, (int)$s
        );
    }

    public function getScore(): int
    {
        return $this->x + $this->m + $this->a + $this->s;
    }

    public function __toString(): string
    {
        return json_encode(['x' => $this->x, 'm' => $this->m, 'a' => $this->a, 's' => $this->s]);
    }
}

final readonly class Problem
{
    /**
     * @param Workflow[] $workflows
     * @param Part[] $parts
     */
    public function __construct(public array $workflows, public array $parts) {}
}

final readonly class PathNode {
    /**
     * @param Rule[] $rulesToFulfill
     * @param string $target
     */
    public function __construct(
        public array $rulesToFulfill,
        public string $target
    ) {}

    public function __toString(): string
    {
        return implode(' -> ', $this->rulesToFulfill) . " ==> $this->target";
    }
}

function getProblem(): Problem
{
    $input = explode("\n", (new InputLoader(__DIR__))->getAsString());
    $parsingWorkflows = true;
    $parts = [];
    $workflowsByLabel = [];
    foreach ($input as $line) {
        if (!$line) {
            $parsingWorkflows = false;
            continue;
        }
        if ($parsingWorkflows) {
            $workflow = Workflow::fromLine($line);
            $workflowsByLabel[$workflow->label] = $workflow;
        } else {
            $parts[] = Part::fromLine($line);
        }
    }

    return new Problem($workflowsByLabel, $parts);
}

