<?php

$header = <<<'EOF'
This file is part of fiedsch/ligaverwaltung-bundle.

(c) 2016-2018 Andreas Fieger

@package Ligaverwaltung
@link https://github.com/fiedsch/contao-ligaverwaltung-bundle/
@license https://opensource.org/licenses/MIT
EOF;

$finder = PhpCsFixer\Finder::create()
    ->exclude('Resources')
    ->in([__DIR__.'/src'])
;

return PhpCsFixer\Config::create()
    ->setRules([
        '@Symfony' => true,
        'function_declaration' => false, // af. override corresponding setting in @Symfony
        '@Symfony:risky' => true,
        '@PHPUnit60Migration:risky' => true,
        'align_multiline_comment' => true,
        'array_syntax' => ['syntax' => 'short'],
        'combine_consecutive_issets' => true,
        'combine_consecutive_unsets' => true,
        'compact_nullable_typehint' => true,
        'general_phpdoc_annotation_remove' => [
            'expectedException',
            'expectedExceptionMessage',
        ],
        'header_comment' => ['header' => $header],
        'heredoc_to_nowdoc' => true,
        'linebreak_after_opening_tag' => true,
        'no_null_property_initialization' => true,
        'no_superfluous_elseif' => true,
        'no_unreachable_default_argument_value' => true,
        'no_useless_else' => true,
        'no_useless_return' => true,
        'ordered_class_elements' => true,
        'ordered_imports' => true,
        'php_unit_strict' => true,
        'phpdoc_add_missing_param_annotation' => true,
        'phpdoc_order' => true,
        'phpdoc_types_order' => [
            'null_adjustment' => 'always_last',
            'sort_algorithm' => 'none',
        ],
        'strict_comparison' => true,
        'strict_param' => true,
        // Remove when https://github.com/FriendsOfPHP/PHP-CS-Fixer/pull/3222 has been merged
        // 'LeoFeyer/optimize_native_functions' => true,
    ])
    //->registerCustomFixers([
    //    new LeoFeyer\PhpCsFixer\OptimizeNativeFunctionsFixer()
    //])
    ->setFinder($finder)
    ->setRiskyAllowed(true)
    ->setUsingCache(false)
    ;