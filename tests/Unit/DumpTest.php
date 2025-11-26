<?php

declare(strict_types=1);

it('can log dumps', function (): void {
    dump('test');

    expect($this->client->sentRequests())->toHaveCount(1);
});

it('can log dumps with a specified dumper format', function (): void {
    ob_start();
    $_SERVER['VAR_DUMPER_FORMAT'] = 'html';
    dump('test 1');
    ob_end_clean();

    expect($this->client->sentRequests())->toHaveCount(1);

    $_SERVER['VAR_DUMPER_FORMAT'] = 'cli';
    dump('test 2');

    expect($this->client->sentRequests())->toHaveCount(2);
});
