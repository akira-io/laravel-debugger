<?php

declare(strict_types=1);

namespace Akira\Debugger\Payloads;

use Spatie\Ray\ArgumentConverter;
use Spatie\Ray\Payloads\Payload;

final class CachePayload extends Payload
{
    /** @var string[] */
    private readonly array $tags;

    public function __construct(private readonly string $type, private readonly string $key, $tags, private readonly mixed $value = null, private readonly ?int $expirationInSeconds = null)
    {
        $this->tags = is_array($tags) ? $tags : [$tags];
    }

    public function getType(): string
    {
        return 'table';
    }

    public function getContent(): array
    {
        $values = array_filter([
            'Event' => '<code>'.$this->type.'</code>',
            'Key' => $this->key,
            'Value' => ArgumentConverter::convertToPrimitive($this->value),
            'Tags' => $this->tags !== [] ? ArgumentConverter::convertToPrimitive($this->tags) : null,
            'Expiration in seconds' => $this->expirationInSeconds,
        ]);

        return [
            'values' => $values,
            'label' => 'Cache',
        ];
    }
}
