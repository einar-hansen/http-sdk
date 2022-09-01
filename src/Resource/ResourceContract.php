<?php

declare(strict_types=1);

namespace EinarHansen\Http\Resource;

use EinarHansen\Http\Service\ServiceContract;

interface ResourceContract
{
    /**
     * Retrieve the built Service from the Resource.
     *
     * @return \EinarHansen\Http\Service\ServiceContract
     */
    public function service(): ServiceContract;
}
