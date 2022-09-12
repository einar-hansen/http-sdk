<?php

declare(strict_types=1);

namespace EinarHansen\Http\Contracts\Pagination;

use Countable;
use IteratorAggregate;
use JsonSerializable;
use Traversable;

/**
 * @extends \IteratorAggregate<int, \EinarHansen\Http\Contracts\Data\Data>
 */
interface Paginator extends Countable, IteratorAggregate, JsonSerializable
{
    /**
     * Determine the current page being paginated.
     *
     * @return int
     */
    public function currentPage(): int;

    /**
     * The next page, or null.
     *
     * @return static|null
     */
    public function nextPage(): ?static;

    /**
     * The previous page, or null.
     *
     * @return static|null
     */
    public function previousPage(): ?static;

    /**
     * Get all of the items being paginated.
     *
     * @return  iterable<int, \EinarHansen\Http\Contracts\Data\Data>
     */
    public function items(): iterable;

    /**
     * Get the "index" of the first item being paginated.
     *
     * @return int
     */
    public function firstItem(): int;

    /**
     * Get the "index" of the last item being paginated.
     *
     * @return int
     */
    public function lastItem(): int;

    /**
     * Determine how many items are being shown per page.
     *
     * @return int
     */
    public function perPage(): int;

    /**
     * Determine if there are more items in the data store.
     *
     * @return bool
     */
    public function hasMorePages(): bool;

    /**
     * Get the number of items for the current page.
     *
     * @return int
     */
    public function count(): int;

    /**
     * Determine if the list of items is empty or not.
     *
     * @return bool
     */
    public function isEmpty(): bool;

    /**
     * Determine if the list of items is not empty.
     *
     * @return bool
     */
    public function isNotEmpty(): bool;

    /**
     * Get an iterator for the items.
     *
     * @see https://www.php.net/manual/en/class.iteratoraggregate
     * @see https://www.php.net/manual/en/iteratoraggregate.getiterator.php
     *
     * @return \ArrayIterator<int, \EinarHansen\Http\Contracts\Data\Data>
     */
    public function getIterator(): Traversable;

    /**
     * Serializes the object to a value that can be serialized natively by json_encode().
     *
     * @see https://www.php.net/manual/en/class.jsonserializable.php
     * @see https://www.php.net/manual/en/jsonserializable.jsonserialize.php
     *
     * @return mixed
     */
    public function jsonSerialize(): mixed;

    /**
     * Get the instance as an array.
     *
     * @return array<int, \EinarHansen\Http\Contracts\Data\Data>
     */
    public function toArray();
}
