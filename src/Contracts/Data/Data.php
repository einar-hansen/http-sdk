<?php

declare(strict_types=1);

namespace EinarHansen\Http\Contracts\Data;

use JsonSerializable;

interface Data extends JsonSerializable
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(): array;

    /**
     * {@inheritDoc}
     */
    public function jsonSerialize(): mixed;
}
