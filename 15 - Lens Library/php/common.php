<?php
declare(strict_types=1);

namespace AoC\Fifteen;

use AoC\Common\InputLoader;

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

final readonly class Lens {
    public function __construct(
        public string $label,
        public int $focalLength
    )
    {}

    public function __toString(): string
    {
        return "$this->label $this->focalLength";
    }
}

final class Box {
    public function __construct(
        public int $boxNumber
    ) {}

    /** @var Lens[] */
    private array $lenses = [];

    public function addLens(Lens $lens): void
    {
        // First see if we have a lens with the same label
        for ($i = 0; $i < count($this->lenses); $i++) {
            if ($this->lenses[$i]->label === $lens->label) {
                $this->lenses[$i] = $lens;
                return;
            }
        }

        // Nope, just add it
        $this->lenses[]= $lens;
    }

    public function removeLens(string $label)
    {
        for ($i = 0; $i < count($this->lenses); $i++) {
            if ($this->lenses[$i]->label === $label) {
                unset($this->lenses[$i]);
                break;
            }
        }

        // Remove any gap we might have just created;
        $this->lenses = array_values($this->lenses);
    }

    public function hasLenses(): bool
    {
        return count($this->lenses) > 0;
    }

    public function getPower(): int
    {
        $total = 0;
        $boxPower = (1 + $this->boxNumber);
        foreach ($this->lenses as $slotNumber => $lens) {
            $total += $boxPower * ($slotNumber + 1) * $lens->focalLength;
        }

        return $total;
    }

    public function __toString(): string
    {
        return "$this->boxNumber: " . implode(', ', $this->lenses);
    }
}

function getInput(): string
{
    return trim((new InputLoader(__DIR__))->getAsString());
}

