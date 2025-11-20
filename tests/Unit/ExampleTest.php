<?php

declare(strict_types=1);

test('example test', function (): void {
    expect(true)->toBeTrue();
});

test('arithmetic', function (): void {
    expect(1 + 1)->toBe(2);
});
