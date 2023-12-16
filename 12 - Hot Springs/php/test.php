<?php
declare(strict_types=1);

namespace AoC\Twelve;

use function AoC\Common\filter;

require_once __DIR__ . '/common.php';

$cases = [
    ['#.#.### 1,1,3', true],
    ['.#...#....###. 1,1,3', true],
    ['.#.###.#.###### 1,3,1,6', true],
    ['####.#...#... 4,1,1', true],
    ['#....######..#####. 1,6,5', true],
    ['.###.##....# 3,2,1', true],
    ['.###.#...#... 4,1,1', false],
    ['#.#..######..#####. 1,6,5', false],
    ['.###.##....## 3,2,1', false],
    ['#....######..#####. 1,6,5,1', false],
];

foreach ($cases as [$line,$expectedValid]) {
   $springRow = SpringRow::fromLine($line);
   if ($expectedValid !== $springRow->isSatisfied()) {
       throw new \Exception("Expected validity !== actual for line '$line'");
   }
}

$cases = [
    ['???.### 1,1,3', 1],
    ['.??..??...?##. 1,1,3', 4],
    ['?#?#?#?#?#?#?#? 1,3,1,6', 1],
    ['????.#...#... 4,1,1', 1],
    ['????.######..#####. 1,6,5', 4],
    ['?###???????? 3,2,1', 10],
];

foreach ($cases as [$line,$expectedOptionCount]) {
    $springRow = SpringRow::fromLine($line);
    $candidates = $springRow->getCandidates();
    $validCandidates = filter($candidates, fn(SpringRow $x) => $x->isSatisfied());
    $actualOptionCount = count($validCandidates);
    if ($expectedOptionCount !== $actualOptionCount) {
        throw new \Exception("Expected $expectedOptionCount options, got $actualOptionCount for line '$line'");
    }
}

$cases = [
    ['.# 1', '.#?.#?.#?.#?.# 1,1,1,1,1'],
    ['???.### 1,1,3', '???.###????.###????.###????.###????.### 1,1,3,1,1,3,1,1,3,1,1,3,1,1,3']
];

foreach ($cases as [$line,$expectedUnfolding]) {
    $springRow = SpringRow::fromLine($line);
    $unfolded = $springRow->unfold();
    if ($expectedUnfolding !== (string) $unfolded) {
        throw new \Exception("Expected unfolding '$expectedUnfolding' for line '$line', got '$unfolded'");
    }
}

echo "tests passed\n";
