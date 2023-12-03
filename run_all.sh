#!/bin/zsh

for dir in [0-9][0-9]*/**/php
do
    echo "$dir A:"
    make --directory "$dir" part_a_example
    echo "$dir B:"
    make --directory "$dir" part_b_example
done
