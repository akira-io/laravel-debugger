<?php

declare(strict_types=1);

use Akira\Debugger\Payloads\MailablePayload;
use Akira\Debugger\Tests\TestClasses\TestMailable;

it('can render a mailable', function () {
    $mailable = new TestMailable;

    $payload = MailablePayload::forMailable($mailable);

    expect(is_string($payload->getContent()['html']))->toBeTrue();
});
