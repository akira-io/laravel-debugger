<?php

declare(strict_types=1);

use Akira\Debugger\Tests\TestClasses\TestMailable;
use Akira\Debugger\Watchers\MailWatcher;
use Illuminate\Mail\Message;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Mail;

it('can send the mailable payload', function (): void {
    ad()->mailable(new TestMailable);

    expect($this->client->sentRequests())->toHaveCount(1);
});

it('can send a logged mailable automatically', function (): void {
    Mail::mailer('log')
        ->cc(['adriaan' => 'adriaan@spatie.be', 'seb@spatie.be'])
        ->bcc(['willem@spatie.be', 'jef@spatie.be'])
        ->to(['freek@spatie.be', 'ruben@spatie.be'])
        ->send(new TestMailable);

    expect($this->client->sentRequests())->toHaveCount(1);
});

it('can send multiple mailable payloads', function (): void {
    ad()->mailable(new TestMailable, new TestMailable);

    expect($this->client->sentPayloads())->toHaveCount(2)
        ->and($this->client->sentRequests())->toHaveCount(1);
});

it('will automatically send mails to ray', function (): void {
    if (! (new MailWatcher)->supportsMessageSendingEvent()) {
        $this->markTestSkipped('This test works for Laravel versions that can automatically log all non-log mails');
    }

    // to addresses in to --> 2 mails will be sent
    Mail::cc(['adriaan' => 'adriaan@spatie.be', 'seb@spatie.be'])
        ->bcc(['willem@spatie.be', 'jef@spatie.be'])
        ->to(['freek@spatie.be', 'ruben@spatie.be'])
        ->send(new TestMailable);

    ad()->stopShowingMails();

    // these should not be logged in Ray
    Mail::cc(['adriaan' => 'adriaan@spatie.be', 'seb@spatie.be'])
        ->bcc(['willem@spatie.be', 'jef@spatie.be'])
        ->to(['freek@spatie.be', 'ruben@spatie.be'])
        ->send(new TestMailable);

    $requests = $this->client->sentRequests();

    expect($requests)->toHaveCount(1);
    expect(Arr::get($requests, '0.payloads.0.origin.file'))->toContain('Mailer.php');
});

it('works with Mail::raw()', function (): void {
    if (! (new MailWatcher)->supportsMessageSendingEvent()) {
        $this->markTestSkipped('This test works for Laravel versions that can automatically log all non-log mails');
    }

    Mail::raw('Hello world', function (Message $message): void {
        $message->to('tim@spatie.be')->from('info@spatie.be');
    });

    $requests = $this->client->sentRequests();

    expect($requests)->toHaveCount(1);
});
