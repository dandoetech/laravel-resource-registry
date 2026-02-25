<?php

use PhpCsFixer\Config;
use PhpCsFixer\Finder;

$finder = Finder::create()
    ->in([__DIR__ . '/src', __DIR__ . '/tests'])
    ->name('*.php')
    ->ignoreVCS(true);

return (new Config())
    ->setRiskyAllowed(true)
    ->setFinder($finder)
    ->setRules([
        '@PSR12' => true,
        'declare_strict_types' => true,
        'strict_param' => true,
        'ordered_imports' => ['sort_algorithm' => 'alpha'],
        'no_unused_imports' => true,
        'no_trailing_whitespace' => true,
        'single_quote' => true,
        'array_syntax' => ['syntax' => 'short'],
        'trailing_comma_in_multiline' => ['elements' => ['arrays', 'arguments', 'parameters']],
        'binary_operator_spaces' => ['operators' => ['=>' => 'align_single_space_minimal']],
        'blank_line_before_statement' => ['statements' => ['return']],
        'phpdoc_align' => ['align' => 'vertical'],
        'phpdoc_no_empty_return' => true,
        'phpdoc_summary' => false,
        'native_function_invocation' => ['include' => ['@internal'], 'scope' => 'namespaced'],
    ]);
