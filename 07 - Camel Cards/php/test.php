<?php
declare(strict_types=1);

namespace AoC\Seven;

require_once __DIR__ . '/common.php';

$cases = [
    'AAAAA' => HandType::FiveOfAKind,
    '22222' => HandType::FiveOfAKind,
    '33222' => HandType::FullHouse,
    '3332A' => HandType::ThreeOfAKind,
    '44445' => HandType::FourOfAKind,
    '34432' => HandType::TwoPair
];

foreach ($cases as $handString => $expectedHandType) {
    $cards = array_map(fn(string $x) => Face::from($x), str_split((string) $handString));
    $hand = new Hand($cards, 1);
    $actual = $hand->handType;
    if ($actual !== $expectedHandType) {
        throw new \Exception("Incorrect Hand Type for $handString. Expected = {$expectedHandType->name}, got = {$actual->name}");
    }
}

$cases = [
    'A' => 1,
    '2' => 2,
    '3' => 3,
    '4' => 4,
    '5' => 5,
    '6' => 6,
    '7' => 7,
    '8' => 8,
    '9' => 9,
    'T' => 10,
    'J' => 11,
    'Q' => 12,
    'K' => 13
];

foreach ($cases as $card => $expectedStr) {
    $face = Face::from((string) $card);
    if ($face->getStrength() !== $expectedStr) {
        throw new \Exception("Expected strength $expectedStr, got {$face->getStrength()}");
    }
}
