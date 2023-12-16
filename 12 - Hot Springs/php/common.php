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
                    $newPossibles[]= array_merge($possibleStatusPrefix, [$status]);
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
        $inGroup = false;
        $groupIndex = 0;
        $currentGroupSizeLimit = null;
        $currentGroupSize = null;
        for ($i = 0; $i < count($this->statuses); $i++) {
            $thisStatus = $this->statuses[$i];
            switch ($thisStatus) {
                case SpringStatus::Good:
                    if ($inGroup) {
                        // not anymore
                        if ($currentGroupSize !== $currentGroupSizeLimit) {
                            return false;
                        }
                        $inGroup = false;
                        $groupIndex += 1;
                        $currentGroupSize = null;
                    }
                    break;
                case SpringStatus::Bad:
                    if ($inGroup) {
                        $currentGroupSize += 1;
                    } else {
                        $inGroup = true;
                        $currentGroupSize = 1;
                        $currentGroupSizeLimit = $this->groups[$groupIndex] ?? 0;
                    }
                    break;
                default:
                    throw new \Exception("Cannot validate row with unknown spring statuses");
            }
        }

        // If we're still in a group at the end, make sure it's the right size
        if ($inGroup) {
            if ($currentGroupSize !== $currentGroupSizeLimit) {
                return false;
            }
            $groupIndex += 1;
        }

        // Ensure we've seen all of our expected groups
        return $groupIndex === count($this->groups);
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
        }

        return $output;
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
