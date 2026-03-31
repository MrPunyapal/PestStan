<?php

declare(strict_types=1);

namespace PestStan\Rules;

use Pest\PendingCalls\TestCall;
use PhpParser\Node;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\ClassConstFetch;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Identifier;
use PhpParser\Node\Name;
use PhpParser\Node\Scalar\String_;
use PHPStan\Analyser\Scope;
use PHPStan\Reflection\ReflectionProvider;
use PHPStan\Rules\IdentifierRuleError;
use PHPStan\Rules\Rule;
use PHPStan\Rules\RuleErrorBuilder;
use PHPStan\Type\ObjectType;

/**
 * Detects coversClass(), coversTrait(), and coversFunction() referencing non-existent symbols.
 *
 * @implements Rule<MethodCall>
 */
final class CoversClassExistsRule implements Rule
{
    /** @var array<string, string> Maps method names to the type of symbol expected */
    private const COVERS_METHODS = [
        'coversClass' => 'Class',
        'coversTrait' => 'Trait',
        'coversFunction' => 'Function',
    ];

    public function __construct(
        private readonly ReflectionProvider $reflectionProvider,
    ) {}

    public function getNodeType(): string
    {
        return MethodCall::class;
    }

    /**
     * @return list<IdentifierRuleError>
     */
    public function processNode(Node $node, Scope $scope): array
    {
        if (! $node->name instanceof Identifier) {
            return [];
        }

        $methodName = $node->name->name;
        if (! isset(self::COVERS_METHODS[$methodName])) {
            return [];
        }

        $callerType = $scope->getType($node->var);
        if (! (new ObjectType(TestCall::class))->isSuperTypeOf($callerType)->yes()) {
            return [];
        }

        $args = $node->getArgs();
        if ($args === []) {
            return [];
        }

        $symbolName = $this->extractClassName($args[0]->value);
        if ($symbolName === null) {
            return [];
        }

        $symbolType = self::COVERS_METHODS[$methodName];

        if ($symbolType === 'Function') {
            if (! $this->reflectionProvider->hasFunction(new Name($symbolName), null)) {
                return [
                    RuleErrorBuilder::message(
                        sprintf('Function %s() referenced in %s() does not exist.', $symbolName, $methodName)
                    )
                        ->identifier('pest.coversFunctionNotFound')
                        ->build(),
                ];
            }

            return [];
        }

        if (! $this->reflectionProvider->hasClass($symbolName)) {
            return [
                RuleErrorBuilder::message(
                    sprintf('%s %s referenced in %s() does not exist.', $symbolType, $symbolName, $methodName)
                )
                    ->identifier('pest.coversClassNotFound')
                    ->build(),
            ];
        }

        return [];
    }

    private function extractClassName(Expr $expr): ?string
    {
        if ($expr instanceof ClassConstFetch && $expr->name instanceof Identifier && $expr->name->toString() === 'class' && $expr->class instanceof Name) {
            return $expr->class->toString();
        }

        if ($expr instanceof String_) {
            return $expr->value;
        }

        return null;
    }
}
