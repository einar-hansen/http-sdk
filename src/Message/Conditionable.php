<?php

namespace EinarHansen\Http\Message;

use Closure;

/**
 * This trait is copied and modified from Laravel.
 */
trait Conditionable
{
    /**
     * Apply the callback if the given "value" is (or resolves to) truthy.
     */
    public function when(mixed $value, callable $callback, callable $default = null): static
    {
        $value = $value instanceof Closure ? $value($this) : $value;

        if ($value) {
            return $callback($this, $value) ?? $this;
        } elseif ($default) {
            return $default($this, $value) ?? $this;
        }

        return $this;
    }

    /**
     * Apply the callback if the given "value" is (or resolves to) falsy.
     */
    public function unless(mixed $value, callable $callback, callable $default = null): static
    {
        $value = $value instanceof Closure ? $value($this) : $value;

        if (! $value) {
            return $callback($this, $value) ?? $this;
        } elseif ($default) {
            return $default($this, $value) ?? $this;
        }

        return $this;
    }
}
