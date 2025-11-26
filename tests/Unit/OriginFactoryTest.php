<?php

declare(strict_types=1);

use Akira\Debugger\OriginFactory;

it('returns correct origin for non-Invador callers', function (): void {
    $expectedLineNumber = __LINE__ + 1;
    $origin = (new OriginFactory)->getOrigin();

    expect($origin->file)->toEqual(__FILE__)
        ->and($origin->lineNumber)->toEqual($expectedLineNumber);
});
