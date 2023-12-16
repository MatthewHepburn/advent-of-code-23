<?php
declare(strict_types=1);

namespace AoC\Twelve;

use AoC\Common\InputLoader;

require_once __DIR__ . '/../../common/php/autoload.php';

enum SpringStatus: string {
    case Good = '.';
    case Bad = '#';
    case Unknown = '?';
}

final readonly class SpringRow {

    /**
     * @param SpringStatus[] $statuses
     * @param int[] $groups
     */
    public function __construct(
        public array $statuses,
        public array $groups
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
        $candidates = $this->getCandidates();
        $validCandidates = 0;
        foreach ($candidates as $candidate) {
            $valid = $candidate->isSatisfied();
            if ($valid) {
                $validCandidates += 1;
            }
        }

        return $validCandidates;
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

    /**
     * @return SpringRow[]
     */
    public function getCandidates(): array
    {
        $possibleStatuses = [[]];
        foreach ($this->statuses as $status) {
            $options = match ($status) {
                SpringStatus::Good, SpringStatus::Bad => [$status],
                SpringStatus::Unknown => [SpringStatus::Good, SpringStatus::Bad]
            };
            $newPossibles = [];
            foreach ($possibleStatuses as $possibleStatusPrefix) {
                foreach ($options as $status) {
                    $newPossible = array_merge($possibleStatusPrefix, [$status]);

                    // Prune our possibles as we go to reduce the growth of our search space
                    if ($this->isValidStatusSequencePrefix($newPossible)) {
                        $newPossibles[]= $newPossible;
                    }
                }
            }
            $possibleStatuses = $newPossibles;
        }

        return array_map(fn(array $statuses) => $this->withStatuses($statuses), $possibleStatuses);
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
