<?php

declare(strict_types=1);

namespace Akira\Debugger\Payloads;

use Spatie\Ray\ArgumentConverter;
use Spatie\Ray\Payloads\Payload;

final class EventPayload extends Payload
{
    private mixed $event = null;

    private array $payload = [];

    public function __construct(private readonly string $eventName, array $payload)
    {
        class_exists($this->eventName)
            ? $this->event = $payload[0]
            : $this->payload = $payload;
    }

    public function getType(): string
    {
        return 'event';
    }

    public function getContent(): array
    {
        return [
            'name' => $this->eventName,
            'event' => $this->event ? ArgumentConverter::convertToPrimitive($this->event) : null,
            'payload' => $this->payload !== [] ? ArgumentConverter::convertToPrimitive($this->payload) : null,
            'class_based_event' => ! is_null($this->event),
        ];
    }
}
