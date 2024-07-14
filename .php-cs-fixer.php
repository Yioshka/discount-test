<?php

declare(strict_types=1);

$finder = (new PhpCsFixer\Finder())
    ->in(__DIR__)
    ->exclude('var')
;

return (new PhpCsFixer\Config())
    ->setCacheFile(__DIR__ . '/var/tmp/.php-cs-fixer.cache')
    ->setRules([
        '@Symfony' => true,
        'final_class' => true,
        'declare_strict_types' => true,
        'blank_line_before_statement' => [
            'statements' => [
                'try',
                'if',
                'return',
                'throw',
                'exit',
                'continue',
                'yield',
            ],
        ],
        'class_attributes_separation' => [
            'elements' => ['method' => 'one'],
        ],
        'concat_space' => [
            'spacing' => 'one',
        ],
        'method_argument_space' => true,
        'not_operator_with_successor_space' => true,
        'method_chaining_indentation' => false,
        'phpdoc_to_comment' => false
    ])
    ->setFinder($finder)
    ->setRiskyAllowed(true)
;
