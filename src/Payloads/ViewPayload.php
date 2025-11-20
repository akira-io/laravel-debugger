<?php

declare(strict_types=1);

namespace Akira\Debugger\Payloads;

use Illuminate\Support\Str;
use Illuminate\View\View;
use Spatie\Ray\ArgumentConverter;
use Spatie\Ray\Payloads\Payload;

final class ViewPayload extends Payload
{
    public function __construct(private readonly View $view) {}

    public function getType(): string
    {
        return 'view';
    }

    public function getContent(): array
    {
        return [
            'view_path' => $this->view->getPath(),
            'view_path_relative_to_project_root' => Str::after($this->pathRelativeToProjectRoot($this->view), '/'),
            'data' => ArgumentConverter::convertToPrimitive($this->getData($this->view)),
        ];
    }

    private function pathRelativeToProjectRoot(View $view): string
    {
        $path = $view->getPath();

        if (Str::startsWith($path, base_path())) {
            return mb_substr($path, mb_strlen(base_path()));
        }

        return $path;
    }

    private function getData(View $view): array
    {
        return collect($view->getData())
            ->filter(fn ($value, $key): bool => ! in_array($key, ['app', '__env', 'obLevel', 'errors']))
            ->toArray();
    }
}
