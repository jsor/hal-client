<?php

$finder = PhpCsFixer\Finder::create()
    ->in(__DIR__)
;

return PhpCsFixer\Config::create()
    ->setUsingCache(false)
    ->setRiskyAllowed(true)
    ->setRules(array(
        '@Symfony' => true,
        'concat_without_spaces' => false,
        'no_unneeded_control_parentheses' => false,
        'phpdoc_summary' => false,
        'phpdoc_var_without_name' => false,
        'simplified_null_return' => false,
        'trailing_comma_in_multiline_array' => false,
        'unalign_double_arrow' => false,
        'unalign_equals' => false,
        'align_double_arrow' => true,
        'align_equals' => true,
        'blank_line_before_return' => true,
        'combine_consecutive_unsets' => true,
        'concat_with_spaces' => true,
        'modernize_types_casting' => true,
        'no_extra_consecutive_blank_lines' => true,
        'no_unused_imports' => true,
        'no_whitespace_in_blank_line' => true,
        'ordered_imports' => true,
        'phpdoc_align' => true,
        'short_array_syntax' => true,
    ))
    ->finder($finder);
