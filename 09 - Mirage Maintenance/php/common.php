<?php
declare(strict_types=1);

namespace AoC\Nine;

use AoC\Common\InputLoader;

require_once __DIR__ . '/../../common/php/autoload.php';

/**
 * @param int[] $sequence
 *
 * @return int[]
 */
function getDiffs(array $sequence): array
{
    $diffs = [];
    $last = null;
    foreach ($sequence as $value) {
        if ($last !== null) {
            $diffs[] = $value - $last;
        }
        $last = $value;
    }

    return $diffs;
}

/**
 * @param int[] $sequence
 *
 * @return bool
 * @throws \Exception
 */
function isZeros(array $sequence): bool
{
    if (!$sequence) {
        throw new \Exception('Got empty sequence, that should not happen');
    }
    foreach ($sequence as $value) {
        if ($value !== 0) {
            return false;
        }
    }

    return true;
}

function getSequences(): array
{
    return (new InputLoader(__DIR__))->getAsIntArrays();
}
