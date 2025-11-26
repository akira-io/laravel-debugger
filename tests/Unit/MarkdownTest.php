<?php

declare(strict_types=1);

use function Pest\version;

it('can render and send markdown', function () {
    ad()->markdown('## Hello World!');

    assertMatchesOsSafeSnapshot($this->client->sentRequests());
})->skip(version_compare(version(), '2.0.0', '>='));

it('can render and send markdown for Pest 2', function () {
    ad()->markdown('## Hello World!');

    assertMatchesOsSafeSnapshot($this->client->sentRequests());
})->skip(version_compare(version(), '2.0.0', '>=') || version_compare(version(), '3.0.0', '<'));
