<?php

$finder = (new PhpCsFixer\Finder())->in(__DIR__);

$rules = [
    '@Symfony' => true,
    // Only allowed if risky rules are enabled: 'declare_strict_types' => true,
    'array_syntax'                                     => ['syntax' => 'short'],
    'array_indentation'                                => true,
    'phpdoc_annotation_without_dot'                    => true,
    'no_alias_language_construct_call'                 => false,
    'phpdoc_summary'                                   => false,
    'no_unneeded_control_parentheses'                  => false,
    'global_namespace_import'                          => ['import_classes' => true, 'import_constants' => true, 'import_functions' => true],
    'concat_space'                                     => ['spacing' => 'one'],
    'nullable_type_declaration_for_default_null_value' => ['use_nullable_type_declaration' => true],
    'no_superfluous_phpdoc_tags'                       => false,
    'binary_operator_spaces'                           => [
        'operators' => [
            '=>' => 'align_single_space_minimal',
        ],
    ],
    'phpdoc_separation' => [],
];

return (new PhpCsFixer\Config())
    ->setRules($rules)
    ->setFinder($finder)
;
