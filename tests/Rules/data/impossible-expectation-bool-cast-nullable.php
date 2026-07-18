<?php

declare(strict_types=1);

// These are ALL valid expectation chains. None should be flagged as impossible.

interface BoolPolicy
{
    public function create(): false;

    public function update(): bool;
}

final class NullableDTO
{
    public ?string $eventPublicId = null;
}

final class SimpleGate
{
    public static function forUser(string $user): self
    {
        return new self;
    }

    public function allows(string $ability): bool
    {
        return true;
    }
}

it('bool-returning method call should not be impossible', function (): void {
    $gate = SimpleGate::forUser('test');

    expect($gate->allows('viewPulse'))->toBeTrue();
});

it('explicit bool cast should not be impossible', function (): void {
    $row = new stdClass;
    $row->cross_tenant = 1;

    expect((bool) $row->cross_tenant)->toBeTrue();
});

it('method returning false literal should not be impossible', function (): void {
    $policy = new class implements BoolPolicy
    {
        public function create(): false
        {
            return false;
        }

        public function update(): bool
        {
            return true;
        }
    };

    expect($policy->create())->toBeFalse();
});

it('method returning bool should not be impossible', function (): void {
    $policy = new class implements BoolPolicy
    {
        public function create(): false
        {
            return false;
        }

        public function update(): bool
        {
            return true;
        }
    };

    expect($policy->update())->toBeTrue();
});

it('nullable string property with toBeString should not be impossible', function (): void {
    $dto = new NullableDTO;
    $dto->eventPublicId = 'some-id';

    expect($dto->eventPublicId)->toBeString();
});

it('nullable string property with toBeNull should not be impossible', function (): void {
    $dto = new NullableDTO;

    expect($dto->eventPublicId)->toBeNull();
});
