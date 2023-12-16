<?php
declare(strict_types=1);

namespace AoC\Fifteen;

use AoC\Common\InputLoader;
use function AoC\Common\filter;

require_once __DIR__ . '/../../common/php/autoload.php';

final class HASHer
{
    public static function hashString(string $input): int
    {
        $chars = str_split($input);
        $currVal = 0;
        foreach ($chars as $char) {
            $currVal = self::hashChar($char, $currVal);
        }

        return $currVal;
    }

    private static function hashChar(string $input, int $currentValue)
    {
        $code = ord($input);
        $currentValue = $currentValue + $code;
        $currentValue = $currentValue * 17;
        $currentValue = $currentValue % 256;

        return $currentValue;
    }


}

function getInput(): string
{
    return trim((new InputLoader(__DIR__))->getAsString());
}

