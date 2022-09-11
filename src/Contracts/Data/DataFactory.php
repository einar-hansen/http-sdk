<?php

declare(strict_types=1);

namespace EinarHansen\Http\Contracts\Data;

interface DataFactory
{
    /**
     * Pass in a formed array and turn it into a Data Object.
     *
     * @param  array<int|string, mixed>  $attributes
     * @return \EinarHansen\Http\Contracts\Data\Data
     */
    public function make(array $attributes): Data;
}
