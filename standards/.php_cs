<?php
$finder = PhpCsFixer\Finder::create()->in(__DIR__ . DIRECTORY_SEPARATOR . '..'
    . DIRECTORY_SEPARATOR . 'web'
    . DIRECTORY_SEPARATOR . 'app'
    . DIRECTORY_SEPARATOR . 'code'
    . DIRECTORY_SEPARATOR . 'local'
    . DIRECTORY_SEPARATOR . 'Peppermint');

return PhpCsFixer\Config::create()
    ->setRules([
        '@PSR2' => true,
        'align_multiline_comment' => [
            'comment_type' => 'all_multiline' // ['all_multiline', 'phpdocs_like', 'phpdocs_only']
        ],
        'array_indentation' => true,
        'array_syntax' => [
            'syntax' => 'short'
        ],
        'backtick_to_shell_exec' => true,
        'binary_operator_spaces' => [
            'default' => 'single_space' // ['align', 'align_single_space', 'align_single_space_minimal', 'no_space', 'single_space', null]
        ],
        'blank_line_after_namespace' => false,
        'blank_line_after_opening_tag' => false,
        'blank_line_before_statement' => [
            'statements' => [
                // 'case',
                // 'continue',
                'declare',
                // 'default',
                'die',
                'do',
                'exit',
                'for',
                'foreach',
                'goto',
                'if',
                'include',
                'include_once',
                'require',
                'require_once',
                'return',
                'switch',
                // 'throw',
                'try',
                'while',
                'yield'
            ]
        ],
        'braces' => [
            'allow_single_line_closure' => true,
            'position_after_anonymous_constructs' => 'same', // ['next', 'same']
            'position_after_control_structures' => 'same', // ['next', 'same']
            'position_after_functions_and_oop_constructs' => 'next' // ['next', 'same']
        ],
        'cast_spaces' => [
            'space' => 'single' // ['none', 'single']
        ],
        'class_attributes_separation' => [
            'elements' => [
                //'const',
                'method',
                'property'
            ]
        ],
        'class_definition' => [
            'multi_line_extends_each_single_line' => false,
            'single_item_single_line' => false,
            'single_line' => true
        ],
        'class_keyword_remove' => false,
        'combine_consecutive_issets' => true,
        'combine_consecutive_unsets' => true,
        'combine_nested_dirname' => false, // - risky rule
        'comment_to_phpdoc' => false, // - risky rule
        'compact_nullable_typehint' => true,
        'concat_space' => [
            'spacing' => 'one' // ['none', 'one']
        ],
        'date_time_immutable' => false, // - risky rule
        'declare_equal_normalize' => [
            'space' => 'single' // ['single', 'none']
        ],
        'declare_strict_types' => false, // - risky rule
        'dir_constant' => false, // - risky rule
        'elseif' => false,
        // 'encoding' =>
        'ereg_to_preg' => false, // - risky rule
        // 'error_suppression' => [ // - risky rule
        //     'mute_deprecation_error' => false,
        //     'noise_remaining_usages' => false,
        //     'noise_remaining_usages_exclude' => [] // eg: unlink
        // ],
        // escape_implicit_backslashes
        'explicit_indirect_variable' => true,
        'explicit_string_variable' => true,
        'final_class' => false, // - risky rule
        'final_internal_class' => false, // - risky rule
        'fopen_flag_order' => false, // - risky rule
        'fopen_flags' => false, // - risky rule
        'full_opening_tag' => true,
        'fully_qualified_strict_types' => true,
        'heredoc_to_nowdoc' => true,
        'implode_call' => false, // - risky rule
        'include' => true,
        'increment_style' => [
            'style' => 'pre' // ['pre', 'post']
        ],
        'indentation_type' => true,
        'is_null' => true, // - risky rule
        'line_ending' => true,
        'linebreak_after_opening_tag' => true,
        'list_syntax' => [
            'syntax' => 'short' // ['short', 'long']
        ],
        'logical_operators' => true, // - risky rule
        'lowercase_cast' => true,
        'lowercase_constants' => true,
        'lowercase_keywords' => true,
        'lowercase_static_reference' => true,
        'magic_constant_casing' => true,
        'magic_method_casing' => true,
        'mb_str_functions' => false, // - risky rule
        'method_argument_space' => [
            'after_heredoc' => false,
            'ensure_fully_multiline' => false,
            'keep_multiple_spaces_after_comma' => false,
            'on_multiline' => 'ignore' // ['ignore', 'ensure_single_line', 'ensure_fully_multiline']
        ],
        'method_chaining_indentation' => true,
        'modernize_types_casting' => false, // - risky rule
        'multiline_comment_opening_closing' => true,
        'multiline_whitespace_before_semicolons' => [
            'strategy' => 'no_multi_line' // ['no_multi_line', 'new_line_for_chained_calls']
        ],
        'native_function_casing' => true,
        'native_function_type_declaration_casing' => true,
        'new_with_braces' => true,
        'no_alternative_syntax' => true,
        'no_binary_string' => true,
        'no_blank_lines_after_class_opening' => false,
        'no_blank_lines_after_phpdoc' => false,
        'no_blank_lines_before_namespace' => false,
        'no_break_comment' => true,
        'no_closing_tag' => true,
        'no_empty_comment' => true,
        'no_empty_phpdoc' => true,
        'no_empty_statement' => true,
        'no_extra_blank_lines' => true,
        'no_leading_import_slash' => false,
        'no_leading_namespace_whitespace' => true,
        'no_mixed_echo_print' => [
            'use' => 'echo' // ['print', 'echo']
        ],
        'no_multiline_whitespace_around_double_arrow' => true,
        'no_null_property_initialization' => true,
        'no_php4_constructor' => true, // - risky rule
        'no_short_bool_cast' => true,
        'no_short_echo_tag' => true,
        'no_singleline_whitespace_before_semicolons' => true,
        'no_spaces_after_function_name' => true,
        'no_spaces_around_offset' => [
            'positions' => [] // ['inside', 'outside']
        ],
        'no_spaces_inside_parenthesis' => true,
        'no_superfluous_elseif' => false,
        'no_superfluous_phpdoc_tags' => false,
        'no_trailing_comma_in_list_call' => true,
        'no_trailing_comma_in_singleline_array' => true,
        'no_trailing_whitespace' => true,
        'no_trailing_whitespace_in_comment' => true,
        'no_unneeded_control_parentheses' => [
            'statements' => [
                'break',
                'clone',
                'continue',
                'echo_print',
                'return',
                'switch_case',
                'yield'
            ]
        ],
        'no_unneeded_curly_braces' => true,
        'no_unneeded_final_method' => true,
        'no_unreachable_default_argument_value' => false,
        'no_unset_cast' => true,
        'no_unset_on_property' => true, // - risky rule
        'no_unused_imports' => true,
        'no_useless_else' => true,
        'no_useless_return' => true,
        'no_whitespace_before_comma_in_array' => [
            'after_heredoc' => false
        ],
        'no_whitespace_in_blank_line' => true,
        'normalize_index_brace' => true,
        'not_operator_with_space' => false,
        'not_operator_with_successor_space' => false,
        'object_operator_without_whitespace' => true,
        // 'ordered_class_elements' => [
        //     'order' => [
        //         'use_trait',
        //         'constant_public',
        //         'constant_protected',
        //         'constant_private',
        //         'property_public',
        //         'property_protected',
        //         'property_private',
        //         'construct',
        //         'destruct',
        //         'magic',
        //         'phpunit',
        //         'method_public',
        //         'method_protected',
        //         'method_private'
        //     ],
        //     'sortAlgorithm' => 'none' // ['none', 'alpha']
        // ],
        'ordered_imports' => [
            'imports_order' => null, // [array, null]
            'sort_algorithm' => 'alpha' // ['alpha', 'length','none']
        ],
        'phpdoc_add_missing_param_annotation' => [
            'only_untyped' => false
        ],
        'phpdoc_align' => [
            'align' => 'left', // ['left', 'vertical']
            'tags' => [
                'param',
                'return',
                'throws',
                'type',
                'var'
            ]
        ],
        'phpdoc_annotation_without_dot' => true,
        'phpdoc_indent' => true,
        'phpdoc_inline_tag' => true,
        'phpdoc_no_access' => true,
        'phpdoc_no_alias_tag' => [
            'replacements' => [
                'property-read' => 'property',
                'property-write' => 'property',
                'type' => 'var',
                'link' => 'see'
            ]
        ],
        'phpdoc_no_empty_return' => false,
        'phpdoc_no_package' => false,
        'phpdoc_no_useless_inheritdoc' => false,
        'phpdoc_order' => true,
        'phpdoc_return_self_reference' => [
            'replacements' => [
                'this' => '$this',
                '@this' => '$this',
                '$self' => 'self',
                '@self' => 'self',
                '$static' => 'static',
                '@static' => 'static'
            ]
        ],
        'phpdoc_scalar' => false,
        'phpdoc_separation' => false,
        'phpdoc_single_line_var_spacing' => true,
        'phpdoc_summary' => true,
        'phpdoc_to_comment' => true,
        'phpdoc_trim' => true,
        'phpdoc_trim_consecutive_blank_line_separation' => true,
        'phpdoc_types' => [
            'groups' => [
                'simple',
                'alias',
                'meta'
            ]
        ],
        'phpdoc_types_order' => [
            'null_adjustment' => 'always_first', // ['always_first', 'always_last', 'none'],
            'sort_algorithm' => 'alpha' // ['alpha', 'none']
        ],
        'phpdoc_var_annotation_correct_order' => true,
        'phpdoc_var_without_name' => true,
        'protected_to_private' => false,
        'return_assignment' => true,
        'self_accessor' => true,
        'semicolon_after_instruction' => true,
        'short_scalar_cast' => true,
        'simple_to_complex_string_variable' => true,
        'simplified_null_return' => true,
        'single_blank_line_at_eof' => true,
        // 'single_blank_line_before_namespace' => true,
        'single_class_element_per_statement' => [
            'elements' => [
                'const',
                'property'
            ]
        ],
        'single_import_per_statement' => true,
        'single_line_after_imports' => true,
        'single_line_comment_style' => [
            'comment_types' => [
                'asterisk',
                'hash'
            ]
        ],
        'single_quote' => [
            'strings_containing_single_quote_chars' => true,
        ],
        'single_trait_insert_per_statement' => false,
        'space_after_semicolon' => [
            'remove_in_empty_for_expressions' => true,
        ],
        'standardize_increment' => true,
        'standardize_not_equals' => true,
        'switch_case_semicolon_to_colon' => true,
        'ternary_operator_spaces' => true,
        'ternary_to_null_coalescing' => true,
        'trailing_comma_in_multiline_array' => false,
        'trim_array_spaces' => true,
        'unary_operator_spaces' => true,
        'visibility_required' => true,
        'whitespace_after_comma_in_array' => true,
        'yoda_style' => [
            'always_move_variable' => false,
            'equal' => false,
            'identical' => false,
            'less_and_greater' => false
        ]
    ])
    ->setLineEnding("\n")
    ->setFinder($finder);
