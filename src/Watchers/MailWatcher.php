<?php

declare(strict_types=1);

namespace Akira\Debugger\Watchers;

use Akira\Debugger\Debugger;
use Akira\Debugger\Payloads\MailablePayload;
use Illuminate\Log\Events\MessageLogged;
use Illuminate\Mail\Events\MessageSending;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Str;
use Spatie\Ray\Settings\Settings;

final class MailWatcher extends Watcher
{
    public function register(): void
    {
        $settings = app(Settings::class);

        if ($settings->send_mails_to_ray ?? true) {
            $this->enable();
        }

        $this->supportsMessageSendingEvent()
            ? $this->registerMessageSendingEventListener()
            : $this->listenForLoggedMails();
    }

    public function listenForLoggedMails(): void
    {
        Event::listen(MessageLogged::class, function (MessageLogged $messageLogged): void {
            if (! $this->enabled()) {
                return;
            }

            if (! $this->concernsLoggedMail($messageLogged)) {
                return;
            }

            /** @var Ray $ray */
            $ray = app(Debugger::class);

            $ray->loggedMail($messageLogged->message);
        });
    }

    public function concernsLoggedMail(MessageLogged $messageLogged): bool
    {
        if (! Str::contains($messageLogged->message, 'Message-ID')) {
            return false;
        }

        return Str::contains($messageLogged->message, 'To:');
    }

    public function supportsMessageSendingEvent(): bool
    {
        return version_compare(app()->version(), '11.0.0', '>=');
    }

    private function registerMessageSendingEventListener(): void
    {
        Event::listen([
            MessageSending::class,
        ], function (MessageSending $event): void {
            if (! $this->enabled()) {
                return;
            }

            $payload = new MailablePayload($event->message->getHtmlBody() ?? $event->message->getTextBody());

            $ray = app(Debugger::class)->sendRequest($payload);

            optional($this->rayProxy)->applyCalledMethods($ray);
        });
    }
}
