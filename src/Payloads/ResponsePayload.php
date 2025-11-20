<?php

declare(strict_types=1);

namespace Akira\Debugger\Payloads;

use Illuminate\Testing\TestResponse;
use Spatie\Ray\ArgumentConverter;
use Spatie\Ray\Payloads\Payload;

final class ResponsePayload extends Payload
{
    private readonly array $headers;

    public function __construct(private readonly int $statusCode, array $headers, private readonly ?string $content, private readonly ?array $json = null)
    {
        $this->headers = $this->normalizeHeaders($headers);
    }

    public static function fromTestResponse(TestResponse $testResponse): self
    {
        return new self(
            $testResponse->getStatusCode(),
            $testResponse->headers->all(),
            $testResponse->content(),
            $json = rescue(fn () => $testResponse->json(), null, false)
        );
    }

    public function getType(): string
    {
        return 'response';
    }

    public function getContent(): array
    {
        return [
            'status_code' => $this->statusCode,
            'headers' => ArgumentConverter::convertToPrimitive($this->headers),
            'content' => $this->content,
            'json' => ArgumentConverter::convertToPrimitive($this->json),
        ];
    }

    private function normalizeHeaders(array $headers): array
    {
        return collect($headers)
            ->map(fn (array $values) => $values[0] ?? null)
            ->filter()
            ->toArray();
    }
}
