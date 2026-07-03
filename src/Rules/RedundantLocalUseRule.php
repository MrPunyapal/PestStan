<?php

declare(strict_types=1);

namespace PestStan\Rules;

use PestStan\Diagnostics\PestDiagnosticIdentifiers;
use PestStan\Type\Pest\PestConfigReader;
use PhpParser\Node;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\CallLike;
use PhpParser\Node\Expr\ClassConstFetch;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Identifier;
use PhpParser\Node\Name;
use PHPStan\Analyser\Scope;
use PHPStan\Rules\IdentifierRuleError;
use PHPStan\Rules\Rule;
use PHPStan\Rules\RuleErrorBuilder;

/**
 * Reports statically known local Pest classes or traits already applied by global use configuration in Pest.php.
 *
 * @implements Rule<CallLike>
 */
final class RedundantLocalUseRule implements Rule
{
    public function __construct(
        private readonly PestConfigReader $pestConfigReader,
    ) {}

    public function getNodeType(): string
    {
        return CallLike::class;
    }

    /**
     * @return list<IdentifierRuleError>
     */
    public function processNode(Node $node, Scope $scope): array
    {
        if (! $this->isLocalUsesCall($node) && ! $this->isLocalPestConfigurationUseCall($node)) {
            return [];
        }

        $globalUses = $this->pestConfigReader->resolveGlobalUses($scope->getFile());
        if ($globalUses === []) {
            return [];
        }

        $globalByClass = [];
        foreach ($globalUses as $globalUse) {
            $globalByClass[strtolower(ltrim($globalUse['class'], '\\'))] = $globalUse['config'];
        }

        $errors = [];
        foreach ($node->getArgs() as $arg) {
            $className = $this->resolveClassName($arg->value, $scope);
            if ($className === null) {
                continue;
            }

            $config = $globalByClass[strtolower(ltrim($className, '\\'))] ?? null;
            if ($config === null) {
                continue;
            }

            $errors[] = RuleErrorBuilder::message(sprintf(
                '%s is already applied globally through %s for this test file.',
                $this->shortName($className),
                $this->displayConfigPath($config),
            ))
                ->identifier(PestDiagnosticIdentifiers::CONFIG_REDUNDANT_LOCAL_USE)
                ->line($arg->getStartLine())
                ->build();
        }

        return $errors;
    }

    private function isLocalUsesCall(Expr $expr): bool
    {
        return $expr instanceof FuncCall
            && $expr->name instanceof Name
            && strtolower($expr->name->toString()) === 'uses';
    }

    private function isLocalPestConfigurationUseCall(Expr $expr): bool
    {
        return $expr instanceof MethodCall
            && $expr->name instanceof Identifier
            && in_array(strtolower($expr->name->toString()), ['use', 'uses'], true)
            && $expr->var instanceof FuncCall
            && $expr->var->name instanceof Name
            && strtolower($expr->var->name->toString()) === 'pest';
    }

    private function resolveClassName(Expr $expr, Scope $scope): ?string
    {
        if (! $expr instanceof ClassConstFetch
            || ! $expr->name instanceof Identifier
            || strtolower($expr->name->toString()) !== 'class'
            || ! $expr->class instanceof Name) {
            return null;
        }

        return $scope->resolveName($expr->class);
    }

    private function shortName(string $className): string
    {
        $parts = explode('\\', $className);

        return end($parts);
    }

    private function displayConfigPath(string $config): string
    {
        $workingDirectory = getcwd();
        $normalizedWorkingDirectory = str_replace('\\', '/', $workingDirectory === false ? '' : $workingDirectory);
        if ($normalizedWorkingDirectory !== '' && str_starts_with($config, $normalizedWorkingDirectory . '/')) {
            return substr($config, strlen($normalizedWorkingDirectory) + 1);
        }

        return $config;
    }
}
