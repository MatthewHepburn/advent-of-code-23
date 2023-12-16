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
EOF, [4], []]
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
        throw new \Exception('Column mismatch!');
    }
    foreach ($actualColumns as $index => $value) {
        if ($expectedColumns[$index] !== $value) {
            throw new \Exception("Got $value, expected {$expectedRows[$index]}");
        }
    }
}

echo "tests passed\n";
