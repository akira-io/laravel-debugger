<?php

declare(strict_types=1);

namespace Akira\Debugger\Payloads;

use Illuminate\Queue\Jobs\Job;
use Spatie\Ray\ArgumentConverter;
use Spatie\Ray\Payloads\Payload;
use Throwable;

final class JobEventPayload extends Payload
{
    /** @var object|mixed */
    private mixed $job;

    private ?Throwable $exception = null;

    public function __construct(private readonly object $event)
    {
        // Some queue drivers use an intermediate job with the orignal job stored inside.
        // For other drivers, the job is not altered, and it can be used directly.
        $this->job = $this->event->job instanceof Job
            ? unserialize($this->event->job->payload()['data']['command'])
            : $this->job = $this->event->job;

        if (property_exists($this->event, 'exception')) {
            $this->exception = $this->event->exception ?? null;
        }
    }

    public function getType(): string
    {
        return 'job_event';
    }

    public function getContent(): array
    {
        return [
            'event_name' => class_basename($this->event),
            'job' => $this->job ? ArgumentConverter::convertToPrimitive($this->job) : null,
            'exception' => $this->exception instanceof Throwable ? ArgumentConverter::convertToPrimitive($this->exception) : null,
        ];
    }
}
