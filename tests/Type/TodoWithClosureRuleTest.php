<?php

declare(strict_types=1);

namespace Tests\Type;

use PestStan\Rules\TodoWithClosureRule;
use PHPStan\Rules\Rule;
use PHPStan\Testing\RuleTestCase;

/**
 * @extends RuleTestCase<TodoWithClosureRule>
 */
class TodoWithClosureRuleTest extends RuleTestCase
{
    protected function getRule(): Rule
    {
        return new TodoWithClosureRule;
    }

    /** @return string[] */
    public static function getAdditionalConfigFiles(): array
    {
        return [
            __DIR__ . '/../extension.neon',
        ];
    }

    public function test_todo_with_closure_body_is_reported(): void
    {
        $this->analyse([
            __DIR__ . '/data/todo-with-closure.php',
        ], [
            [
                "Test 'has closure and todo' is marked as todo() but still has a closure body — the code will not execute.",
                6,
                'Remove the closure body or remove ->todo() to execute the test.',
            ],
            [
                "Test 'has closure and todo' is marked as todo() but still has a closure body — the code will not execute.",
                10,
                'Remove the closure body or remove ->todo() to execute the test.',
            ],
        ]);
    }
}
