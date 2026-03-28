<?php

declare(strict_types=1);

namespace PestStan\Type\Pest;

use PhpParser\Node;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\PropertyFetch;
use PHPStan\Analyser\Error;
use PHPStan\Analyser\IgnoreErrorExtension;
use PHPStan\Analyser\Scope;

final class PestInternalClassAccessIgnoreExtension implements IgnoreErrorExtension
{
    private const SUPPRESSED_IDENTIFIERS = [
        'property.internalClass',
        'method.internalClass',
        'method.internalTrait',
    ];

    public function shouldIgnore(Error $error, Node $node, Scope $scope): bool
    {
        if (! in_array($error->getIdentifier(), self::SUPPRESSED_IDENTIFIERS, true)) {
            return false;
        }

        if (! $node instanceof MethodCall && ! $node instanceof PropertyFetch) {
            return false;
        }

        return $this->isPestInternalType($scope->getType($node->var)->getReferencedClasses());
    }

    /** @param list<non-empty-string> $classes */
    private function isPestInternalType(array $classes): bool
    {
        foreach ($classes as $class) {
            if (str_starts_with($class, 'Pest\\')) {
                return true;
            }
        }

        return false;
    }
}
