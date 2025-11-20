<?php

declare(strict_types=1);

namespace Akira\Debugger\Payloads;

use Illuminate\Database\Eloquent\Model;
use Spatie\Ray\ArgumentConverter;
use Spatie\Ray\Payloads\Payload;

final class ModelPayload extends Payload
{
    public function __construct(private readonly ?Model $model) {}

    public function getType(): string
    {
        return 'eloquent_model';
    }

    public function getContent(): array
    {
        if (! $this->model instanceof Model) {
            return [];
        }

        $content = [
            'class_name' => $this->model::class,
            'attributes' => ArgumentConverter::convertToPrimitive($this->model->attributesToArray()),
        ];

        $relations = $this->model->relationsToArray();

        if (count($relations) !== 0) {
            $content['relations'] = ArgumentConverter::convertToPrimitive($relations);
        }

        return $content;
    }
}
