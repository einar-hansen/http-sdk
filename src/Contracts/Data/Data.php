<?php

declare(strict_types=1);

namespace EinarHansen\Http\Contracts\Data;

interface Data
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(): array;
}
