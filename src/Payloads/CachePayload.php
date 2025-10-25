<?php

declare(strict_types=1);

namespace Akira\Debugger\Payloads;

use Spatie\Ray\ArgumentConverter;
use Spatie\Ray\Payloads\Payload;

class CachePayload extends Payload
{
    protected string $type;

    /** @var string[] */
    protected array $tags;

    protected string $key;

    protected mixed $value;

    protected ?int $expirationInSeconds;

    public function __construct(string $type, string $key, $tags, $value = null, ?int $expirationInSeconds = null)
    {
        $this->type = $type;

        $this->key = $key;

        $this->tags = is_array($tags) ? $tags : [$tags];

        $this->value = $value;

        $this->expirationInSeconds = $expirationInSeconds;
    }

    public function getType(): string
    {
        return 'table';
    }

    public function getContent(): array
    {
        $values = array_filter([
            'Event' => '<code>' . $this->type . '</code>',
            'Key' => $this->key,
            'Value' => ArgumentConverter::convertToPrimitive($this->value),
            'Tags' => count($this->tags) ? ArgumentConverter::convertToPrimitive($this->tags) : null,
            'Expiration in seconds' => $this->expirationInSeconds,
        ]);

        return [
            'values' => $values,
            'label' => 'Cache',
        ];
    }
}
