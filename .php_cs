<?php

$header = <<<EOF
This file is part of the Proximum Vimeet project.

Copyright (C) Proximum

@author Elao <contact@elao.com>
EOF;

Symfony\CS\Fixer\Contrib\HeaderCommentFixer::setHeader($header);

return Symfony\CS\Config\Config::create()
    // use default SYMFONY_LEVEL and extra fixers:
    ->fixers([
        '-concat_without_spaces',
        '-phpdoc_short_description',
        '-pre_increment',
        '-unalign_double_arrow',
        '-unalign_equals',
        'align_double_arrow',
        'align_equals',
        'concat_with_spaces',
        'header_comment',
        'ordered_use',
        'phpdoc_order',
        'short_array_syntax',
    ])
    ->setUsingCache(true)
    ->finder(
        Symfony\CS\Finder\DefaultFinder::create()
            ->in('src')
    )
;
