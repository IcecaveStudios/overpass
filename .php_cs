<?php

$finder = Symfony\CS\Finder\DefaultFinder::create()
    ->in(__DIR__ . '/src')
    ->in(__DIR__ . '/test/src')
    ->in(__DIR__ . '/test/suite');

return Symfony\CS\Config\Config::create()
    ->fixers(array(
        '-concat_without_spaces',
        '-new_with_braces',
        'align_double_arrow',
        'align_equals',
        'ordered_use',
        'short_array_syntax',
    ))
    ->finder($finder);
