<?php

declare(strict_types=1);

namespace Akira\Debugger\Payloads;

use Illuminate\Mail\Mailable;
use Spatie\Ray\Payloads\Payload;
use Throwable;

final class MailablePayload extends Payload
{
    public function __construct(private readonly string $html, private readonly ?Mailable $mailable = null) {}

    public static function forMailable(Mailable $mailable): self
    {
        return new self(self::renderMailable($mailable), $mailable);
    }

    public function getType(): string
    {
        return 'mailable';
    }

    public function getContent(): array
    {
        $content = [
            'html' => $this->html,
            'from' => [],
            'to' => [],
            'cc' => [],
            'bcc' => [],
        ];

        if ($this->mailable instanceof Mailable) {
            return array_merge($content, [
                'mailable_class' => $this->mailable::class,
                'from' => $this->convertToPersons($this->mailable->from),
                'subject' => $this->mailable->subject,
                'to' => $this->convertToPersons($this->mailable->to),
                'cc' => $this->convertToPersons($this->mailable->cc),
                'bcc' => $this->convertToPersons($this->mailable->bcc),
            ]);
        }

        return $content;
    }

    private static function renderMailable(Mailable $mailable): string
    {
        try {
            return $mailable->render();
        } catch (Throwable $exception) {
            return "Mailable could not be rendered because {$exception->getMessage()}";
        }
    }

    private function convertToPersons(array $persons): array
    {
        return collect($persons)
            ->map(fn (array $person): array => [
                'email' => $person['address'],
                'name' => $person['name'] ?? '',
            ])
            ->toArray();
    }
}
