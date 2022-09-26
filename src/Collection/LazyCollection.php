<?php

declare(strict_types=1);

namespace EinarHansen\Http\Collection;

use ArrayIterator;
use Closure;
use Countable;
use EinarHansen\Http\Contracts\Data\Data;
use Generator;
use Iterator;
use IteratorAggregate;
use JsonSerializable;
use Traversable;

/**
 * @implements IteratorAggregate<int, Data>
 */
class LazyCollection implements Countable, IteratorAggregate, JsonSerializable
{
    /**
     * Pass closure or array if you want to run the collection multiple times, or
     * pass a Generator if you only want to run it one
     *
     * @param  Traversable<int, Data>|array<int, Data>|(Closure(): Generator<int, Data>) $items
     */
    public function __construct(
        public readonly Closure|iterable $items
    ) {
    }

    /**
     * @return array<int, Data>
     */
    public function items(): iterable
    {
        return $this->toArray();
    }

    /**
     * Get the first item in the collection
     */
    public function first(): ?Data
    {
        $needle = null;

        foreach ($this as $key => $value) {
            return $value;
        }

        return null;
    }

    /**
     * Get the last item in the collection
     */
    public function last(): ?Data
    {
        $needle = null;

        foreach ($this as $key => $value) {
            $needle = $value;
        }

        return $needle;
    }

    /**
     * Get an item by key.
     */
    public function get(int $key): ?Data
    {
        foreach ($this as $outerKey => $outerValue) {
            if ($outerKey == $key) {
                return $outerValue;
            }
        }

        return null;
    }

    /**
     * Reset the keys on the underlying array.
     *
     * @return static
     */
    public function values(): static
    {
        return new LazyCollection(
            items: function () {
                foreach ($this as $item) {
                    yield $item;
                }
            },
        );
    }

    public function count(): int
    {
        if (is_array($this->items)) {
            return count($this->items);
        }

        return iterator_count($this->getIterator());
    }

    public function isEmpty(): bool
    {
        $iterator = $this->getIterator();
        if ($iterator instanceof Iterator) {
            return $iterator->valid();
        }

        return $this->count() > 0;
    }

    public function isNotEmpty(): bool
    {
        return ! $this->isEmpty();
    }

    /**
     * @return Traversable<int, Data>
     */
    public function getIterator(): Traversable
    {
        return $this->makeIterator($this->items);
    }

    /**
     * Make an iterator from the given source.
     *
     * @param  Traversable<int, Data>|array<int, Data>|(Closure(): Generator<int, Data>) $source
     * @return Traversable<int, Data>
     */
    protected function makeIterator($source)
    {
        if ($source instanceof IteratorAggregate) {
            /** @var Traversable<int, Data> $items */
            $items = $source->getIterator();

            return $items;
        }

        if (is_array($source)) {
            return new ArrayIterator($source);
        }

        if ($source instanceof Traversable) {
            return $source;
        }

        return $source();
    }

    public function jsonSerialize(): mixed
    {
        return $this->toArray();
    }

    /**
     * Make an iterator from the given source.
     *
     * @return array<int, Data>
     */
    public function toArray(): array
    {
        if (is_array($this->items)) {
            return $this->items;
        }

        return iterator_to_array(
            iterator:  $this->getIterator(),
            preserve_keys:  false
        );
    }
}
