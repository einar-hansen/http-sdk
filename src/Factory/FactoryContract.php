<?php

declare(strict_types=1);

namespace EinarHansen\Http\Factory;

use EinarHansen\Http\Data\DataContract;

interface FactoryContract
{
    /**
     * Pass in a formed array and turn it into a Data Object.
     *
     * @param  array<string, mixed>  $attributes
     * @return \EinarHansen\Http\Data\DataContract
     */
    public function make(array $attributes): DataContract;
}
