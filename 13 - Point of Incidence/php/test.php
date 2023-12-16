<?php
declare(strict_types=1);

namespace AoC\Thirteen;

require_once __DIR__ . '/common.php';

$cases = [
    [<<<'EOF'
#.##..##.
..#.##.#.
##......#
##......#
..#.##.#.
..##..##.
#.#.##.#.
EOF, [], [5]],
    [<<<'EOF'
#...##..#
#....#..#
..##..###
#####.##.
#####.##.
..##..###
#....#..#
EOF, [4], []],
    [<<<'EOF'
#...
..##
####
EOF, [], [3]],
    [<<<'EOF'
..#.
##.#
####
EOF, [], [1]]
];

foreach ($cases as [$string,$expectedRows, $expectedColumns]) {
    $lines = explode(PHP_EOL, $string);
    $map = MirrorMap::fromLines($lines);
    $actualRows = $map->findReflectionRows();
    if (count($actualRows) !== count($expectedRows)) {
        throw new \Exception('Row mismatch!');
    }
    foreach ($actualRows as $index => $value) {
        if ($expectedRows[$index] !== $value) {
            throw new \Exception("Got $value, expected {$expectedRows[$index]}");
        }
    }


    $actualColumns = $map->findReflectionColumns();
    if (count($actualColumns) !== count($expectedColumns)) {
        echo "Expected = " . json_encode($expectedColumns) . "\n";
        echo "Actual = " . json_encode($actualColumns) . "\n";
        throw new \Exception('Column mismatch! Expected ' . count($expectedColumns) . ' cols, got ' . count($actualColumns));
    }
    foreach ($actualColumns as $index => $value) {
        if ($expectedColumns[$index] !== $value) {
            throw new \Exception("Got $value, expected {$expectedColumns[$index]}");
        }
    }
}

echo "tests passed\n";
