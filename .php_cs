<?php

$finder = PhpCsFixer\Finder::create()
    ->in([__DIR__ . '/src', __DIR__ . '/tests'])
    ->exclude(['fixtures', 'DataFixtures', 'expected', 'var'])
;

return PhpCsFixer\Config::create()
    ->setFinder($finder)
    ->setRules([
        '@Symfony' => true,
        'psr0' => false,
        'concat_space' => ['spacing' => 'one'],
        'phpdoc_summary' => false,
        'phpdoc_annotation_without_dot' => false,
        'phpdoc_order' => true,
        'phpdoc_inline_tag' => true,
        'binary_operator_spaces' => ['default' => null],
        'array_syntax' => ['syntax' => 'short'],
        'ordered_imports' => true,
        'return_type_declaration' => ['space_before' => 'none'],
    ])
;
