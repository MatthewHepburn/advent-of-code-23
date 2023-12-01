<?php

function filter(array $arr, callable $callable = null): array
{
    return array_values(array_filter($arr, $callable));
}
