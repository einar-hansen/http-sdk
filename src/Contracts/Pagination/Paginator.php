<?php

declare(strict_types=1);

namespace EinarHansen\Http\Contracts\Pagination;

use Countable;
use IteratorAggregate;
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
    public function currentPage();

    /**
     * The URL for the next page, or null.
     *
     * @return int|null
     */
    public function nextPage();

    /**
     * Get the URL for the previous page, or null.
     *
     * @return string|null
     */
    public function previousPage();

    /**
     * Get all of the items being paginated.
     *
     * @return  iterable<int, \EinarHansen\Http\Contracts\Data\Data>
     */
    public function items();

    /**
     * Get the "index" of the first item being paginated.
     *
     * @return int
     */
    public function firstItem();

    /**
     * Get the "index" of the last item being paginated.
     *
     * @return int
     */
    public function lastItem();

    /**
     * Determine how many items are being shown per page.
     *
     * @return int
     */
    public function perPage();

    /**
     * Determine if there are enough items to split into multiple pages.
     *
     * @return bool
     */
    public function hasPages();

    /**
     * Determine if there are more items in the data store.
     *
     * @return bool
     */
    public function hasMorePages();

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
    public function isEmpty();

    /**
     * Determine if the list of items is not empty.
     *
     * @return bool
     */
    public function isNotEmpty();

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
