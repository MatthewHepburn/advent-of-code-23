<?php
declare(strict_types=1);

namespace AoC\Twelve;

use AoC\Common\InputLoader;
use function AoC\Common\filter;

require_once __DIR__ . '/../../common/php/autoload.php';

enum SpringStatus: string {
    case Good = '.';
    case Bad = '#';
    case Unknown = '?';
}

final class SpringRow {
    public int $weight = 1;

    /**
     * @param SpringStatus[] $statuses
     * @param int[] $groups
     */
    public function __construct(
        public readonly array $statuses,
        public readonly array $groups
    ) { }

    public static function fromLine(string $line): self
    {
        [$statusChars, $groupsString] = explode(' ', $line);
        $statuses = array_map([SpringStatus::class, 'from'], str_split($statusChars));
        $groups = array_map(fn(string $x) => (int) $x, explode(',', $groupsString));

        return new self($statuses, $groups);
    }


    public function getOptionCount(): int
    {
        return $this->getCandidates();
    }

    /**
     * @param SpringStatus[] $statuses
     *
     * @return self
     */
    public function withStatuses(array $statuses): self
    {
        return new self($statuses, $this->groups);
    }

    public function getCandidates(): int
    {
        $possibleStatuses = [[
            'weight' => 1,
            'sequence' => []
        ]];
        foreach ($this->statuses as $status) {
            $options = match ($status) {
                SpringStatus::Good, SpringStatus::Bad => [$status],
                SpringStatus::Unknown => [SpringStatus::Good, SpringStatus::Bad]
            };
            $newPossiblesBySignature = [];
            foreach ($possibleStatuses as $arr) {
                $possibleStatusPrefix = $arr['sequence'];
                $weight = $arr['weight'];
                foreach ($options as $status) {
                    $newPossible = array_merge($possibleStatusPrefix, [$status]);

                    if (!$this->isValidStatusSequencePrefix($newPossible)) {
                        continue;
                    }

                    // Prune our possibles as we go to reduce the growth of our search space
                    $signature = $this->getSequenceSignature($newPossible);
                    if (isset($newPossiblesBySignature[$signature])) {
                        // echo "Existing signature: $signature, has weight {$newPossiblesBySignature[$signature]['weight']}\n;";
                        $newPossiblesBySignature[$signature]['weight'] += $weight;
                    } else {
                        // echo "New signature: $signature\n;";
                        $newPossiblesBySignature[$signature] = [
                            'sequence' => $newPossible,
                            'weight'   => $weight
                        ];
                    }
                }
            }
            $possibleStatuses = array_values($newPossiblesBySignature);
        }

        $total = 0;
        foreach ($possibleStatuses as $arr) {
            $finishedRow = $this->withStatuses($arr['sequence']);
            $finishedRow->weight = $arr['weight'];
            if ($finishedRow->isSatisfied()) {
                $total += $arr['weight'];
            }
        }

        return $total;
    }

    public function __toString(): string
    {
        $output = '';
        foreach ($this->statuses as $status) {
            $output .= $status->value;
        }

        $output .= ' ' . implode(',', $this->groups);
        return $output;
    }

    public function isSatisfied(): bool
    {
        $groupSizes = $this->getGroupSizes($this->statuses);
        if (count($groupSizes) !== count($this->groups)) {
            return false;
        }

        for ($i = 0; $i < count($this->groups); $i++) {
            if ($groupSizes[$i] !== $this->groups[$i]) {
                return false;
            }
        }

        return true;
    }

    public function unfold(): self
    {
        $newStatuses = [];
        $newGroups = [];
        for ($i = 0; $i < 5; $i++) {
            $join = $i !== 0 ? [SpringStatus::Unknown] : [];
            $newStatuses = array_merge($newStatuses, $join, $this->statuses);
            $newGroups = array_merge($newGroups, $this->groups);
        }

        return new self($newStatuses, $newGroups);
    }

    /**
     * @param SpringStatus[] $statuses
     *
     * @return int[]
     */
    private function getGroupSizes(array $statuses): array {
        $output = [];
        $lastStatus = SpringStatus::Good;
        $currentSize = 0;

        foreach ($statuses as $status) {
            if ($lastStatus === SpringStatus::Bad && $status === SpringStatus::Good) {
                $output[]= $currentSize;
                $currentSize = 0;
            } else if ($status === SpringStatus::Bad) {
                $currentSize += 1;
            }

            $lastStatus = $status;
        }

        // Ended in a group, record that
        if ($currentSize > 0) {
            $output[]= $currentSize;
        }

        return $output;
    }

    /**
     * @param SpringStatus[] $statusPrefix
     *
     * @return bool
     */
    private function isValidStatusSequencePrefix(array $statusPrefix): bool
    {
        $groupSizes = $this->getGroupSizes($statusPrefix);
        if (count($groupSizes) > count($this->groups)) {
            return false;
        }

        // Compare up to the second last group - last group could still grow
        for ($i = 0; $i < count($groupSizes) - 1; $i++) {
            if ($groupSizes[$i] !== $this->groups[$i]) {
                return false;
            }
        }

        return true;
    }

    /**
     * @param SpringStatus[] $statusSequence
     *
     * @return string
     */
    private function getSequenceSignature(array $statusSequence): string
    {
        $brokenTotal = count(filter($statusSequence, fn(SpringStatus $x) => $x === SpringStatus::Bad));
        $groups = $this->getGroupSizes($statusSequence);
        $groupCount = count($groups);
        $inGroup = ($statusSequence[count($statusSequence) - 1] ?? SpringStatus::Good) === SpringStatus::Bad;
        $currentGroupSize = $inGroup ? ($groups[count($groups) - 1] ?? 0) : 0;

        return "$brokenTotal-$groupCount-$currentGroupSize";

    }
}

/**
 * @return SpringRow[]
 */
function getSpringRows(): array
{
    $lines = (new InputLoader(__DIR__))->getAsStrings();

    return array_map([SpringRow::class, 'fromLine'], $lines);
}

/**
 * @return \Generator<SpringRow>
 */
function getSpringRowsUnfolded(): \Generator
{
    $lines = (new InputLoader(__DIR__))->getAsStrings();

    foreach ($lines as $line) {
        $row = SpringRow::fromLine($line)->unfold();
        yield $row;
    }
}
