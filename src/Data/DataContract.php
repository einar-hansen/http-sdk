<?php

declare(strict_types=1);

namespace EinarHansen\Http\Data;

interface DataContract
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(): array;
}
