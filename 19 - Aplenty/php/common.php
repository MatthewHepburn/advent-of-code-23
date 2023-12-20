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
}
final readonly class Workflow
{
    /**
     * @param string $label
     * @param Rule[] $rules
     * @param string $defaultTarget
     */
    public function __construct(public string $label, private array $rules, private string $defaultTarget) {}

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

        return new self($label, $rules, $default);
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

