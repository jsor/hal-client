<?php

$finder = PhpCsFixer\Finder::create()
    ->in(__DIR__);

return PhpCsFixer\Config::create()
    ->setUsingCache(false)
    ->setRiskyAllowed(true)
    ->setRules(array(
        '@Symfony' => true,
        'array_syntax' => [
            'syntax' => 'short'
        ],
        'binary_operator_spaces' => array(
            'align_double_arrow' => true,
            'align_equals' => true,
        ),
        'concat_space' => array(
            'spacing' => 'one'
        ),
        'no_unneeded_control_parentheses' => false,
        'phpdoc_summary' => false,
        'phpdoc_var_without_name' => false,
        'simplified_null_return' => false,
        'trailing_comma_in_multiline_array' => false,
    ))
    ->setFinder($finder);
