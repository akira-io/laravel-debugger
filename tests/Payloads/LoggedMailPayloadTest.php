<?php

declare(strict_types=1);

use Akira\Debugger\Payloads\LoggedMailPayload;

it('can parse a logged mail', function (): void {
    $loggedMail = <<<'EOD'
Message-ID: <780b20b2a80adefb6ebb6c9fb7d15d8a@swift.generated>
Date: Fri, 26 Nov 2025 08:54:24 +0000
Subject: Test Mailable
From: Example <hello@example.com>
To: Geral <geral@akira-io.com>, ruben@akira-io.com
Cc: adriaan@akira-io.com, Seb <seb@akira-io.com>
Bcc: willem@akira-io.com
MIME-Version: 1.0
Content-Type: multipart/alternative;

# fake mail
EOD;

    $payload = LoggedMailPayload::forLoggedMail($loggedMail);

    expect([
        'html' => '# fake mail',
        'subject' => 'Test Mailable',
        'from' => [
            [
                'name' => 'Example',
                'email' => 'hello@example.com',
            ],
        ],
        'to' => [
            [
                'name' => 'Geral',
                'email' => 'geral@akira-io.com',
            ],
            [
                'name' => '',
                'email' => 'ruben@akira-io.com',
            ],
        ],
        'cc' => [
            [
                'name' => '',
                'email' => 'adriaan@akira-io.com',
            ],
            [
                'name' => 'Seb',
                'email' => 'seb@akira-io.com',
            ],
        ],
        'bcc' => [
            [
                'name' => '',
                'email' => 'willem@akira-io.com',
            ],
        ],
    ])->toEqual($payload->getContent());
});

it('can omit some headers in a parsed mail', function (): void {
    $loggedMail = <<<'EOD'
From: Example <hello@example.com>
To: Geral <geral@akira-io.com>
Content-Type: multipart/alternative;

# fake mail
EOD;

    $payload = LoggedMailPayload::forLoggedMail($loggedMail);

    expect([
        'html' => '# fake mail',
        'subject' => null,
        'from' => [
            [
                'name' => 'Example',
                'email' => 'hello@example.com',
            ],
        ],
        'to' => [
            [
                'name' => 'Geral',
                'email' => 'geral@akira-io.com',
            ],
        ],
        'cc' => [],
        'bcc' => [],
    ])->toEqual($payload->getContent());
});
