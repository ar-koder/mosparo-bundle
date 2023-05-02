<?php

$finder = PhpCsFixer\Finder::create()
    ->in(__DIR__)
    ->ignoreDotFiles(true)
    ->ignoreVCS(true)
    ->exclude([
        'vendor'
    ])
    ->files()
    ->name('*.php')
;

$config = new PhpCsFixer\Config();

$header = <<<'EOT'
@package   MosparoBundle
@author    Arnaud RITTI <arnaud.ritti@gmail.com>
@copyright <YEAR> Arnaud RITTI
@license   MIT <https://github.com/arnaud-ritti/mosparo-bundle/blob/main/LICENSE.md>
@link      https://github.com/arnaud-ritti/mosparo-bundle
EOT;

$header = str_replace('<YEAR>', date('Y'), $header);

return $config
    ->setUsingCache(true)
    ->setRiskyAllowed(true)
    ->setFinder($finder)
    ->setRules([
        '@PhpCsFixer' => true,
        '@PSR12' => true,
        '@Symfony' => true,
        '@Symfony:risky' => true,
        '@PHPUnit48Migration:risky' => true,
        'array_syntax' => ['syntax' => 'short'],
        'fopen_flags' => false,
        'ordered_imports' => true,
        'header_comment' => [
            'header' => $header,
            'comment_type' => 'PHPDoc',
            'location' => 'after_open',
            'separate' => 'both'
        ],
        'protected_to_private' => false,
        // Part of @Symfony:risky in PHP-CS-Fixer 2.13.0. To be removed from the config file once upgrading
        'native_function_invocation' => ['include' => ['@compiler_optimized'], 'scope' => 'namespaced', 'strict' => true],
        // Part of future @Symfony ruleset in PHP-CS-Fixer To be removed from the config file once upgrading
        'phpdoc_types_order' => ['null_adjustment' => 'always_last', 'sort_algorithm' => 'none'],
        'single_line_throw' => false,
        // this must be disabled because the output of some tests include NBSP characters
        'non_printable_character' => false,
        'blank_line_between_import_groups' => false,
        'no_trailing_comma_in_singleline' => false,
        'declare_strict_types' => true,
        'php_unit_test_class_requires_covers' => false,
        'method_chaining_indentation' => false,
        'concat_space' => ['spacing' => 'one']
    ])
;
