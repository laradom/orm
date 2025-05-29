<?php

declare(strict_types=1);

use PhpCsFixer\Config;
use PhpCsFixer\Finder;

$finder = (new Finder())
    ->in(__DIR__)
    ->exclude([
        'vendor',
        'storage',
        'examples',
    ])
    ->notPath([
        'bootstrap/autoload.php',
    ])
    ->append([
        __FILE__,
    ]);

return (new Config())
    ->setRules([
        '@PSR12' => true,
        '@PSR12:risky' => true,
        '@PHP81Migration' => true,
        '@PhpCsFixer' => true,
        '@PhpCsFixer:risky' => true,
        'concat_space' => [
            'spacing' => 'one',
        ],
        'cast_spaces' => [
            'space' => 'single',
        ],
        'binary_operator_spaces' => true,
        'phpdoc_separation' => true,
        'phpdoc_types_order' => [
            'sort_algorithm' => 'none',
            'null_adjustment' => 'always_last',
        ],
        'phpdoc_align' => [
            'align' => 'left',
        ],
        'operator_linebreak' => true,
        'global_namespace_import' => [
            'import_classes' => true,
            'import_constants' => true,
            'import_functions' => true,
        ],
        'blank_line_before_statement' => [
            'statements' => [
                'return',
                'throw',
                'try',
                'if',
            ],
        ],
        'multiline_whitespace_before_semicolons' => [
            'strategy' => 'no_multi_line',
        ],
        'increment_style' => [
            'style' => 'post',
        ],
        'ordered_imports' => [
            'sort_algorithm' => 'alpha',
            'imports_order' => ['class', 'function', 'const'],
        ],
        'trailing_comma_in_multiline' => [
            'elements' => ['arrays', 'parameters', 'arguments'],
        ],
        'phpdoc_to_property_type' => true,
        'phpdoc_to_return_type' => true,
        'array_indentation' => true,
        'method_chaining_indentation' => true,
        'no_useless_else' => true,
        'no_useless_return' => true,
        'explicit_string_variable' => true,
        'fully_qualified_strict_types' => true,

        'yoda_style' => false,
        'phpdoc_summary' => false,
        'self_accessor' => false,
        'phpdoc_to_comment' => false,
        'final_class' => false,
        'final_public_method_for_abstract_class' => false,
        'self_static_accessor' => false,
        'static_lambda' => false,
        'combine_consecutive_issets' => false,
        'combine_consecutive_unsets' => false,
        'empty_loop_body' => false,
        'no_null_property_initialization' => false,
        'php_unit_internal_class' => false,
        'php_unit_test_class_requires_covers' => false,
        'phpdoc_add_missing_param_annotation' => false,
        'no_unreachable_default_argument_value' => false,
        'php_unit_strict' => false,
        'php_unit_test_case_static_method_calls' => false,
        'native_function_invocation' => false,
        'native_constant_invocation' => false,
        'line_ending' => false,
        'heredoc_indentation' => false,
    ])
    ->setCacheFile(__DIR__ . '/.php-cs-fixer.cache')
    ->setFinder($finder)
    ->setRiskyAllowed(true);
