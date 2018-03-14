<?php
declare(strict_types=1);

namespace predaddy\presentation;

use precore\lang\ObjectInterface;
use Traversable;

/**
 * Provides a page representation wich include the content and paging information as well.
 * Useful for pagination.
 *
 * @author Janos Szurovecz <szjani@szjani.hu>
 */
interface Page extends Traversable, ObjectInterface
{
    /**
     * @return int
     */
    public function getNumber() : int;

    /**
     * @return int
     */
    public function getSize() : int;

    /**
     * @return int
     */
    public function getTotalPages() : int;

    /**
     * @return int
     */
    public function getTotalElements() : int;

    /**
     * @return boolean
     */
    public function hasPreviousPage() : bool;

    /**
     * @return boolean
     */
    public function isFirstPage() : bool;

    /**
     * @return boolean
     */
    public function hasNextPage() : bool;

    /**
     * @return boolean
     */
    public function isLastPage() : bool;

    /**
     * @return Pageable
     */
    public function nextPageable() : ?Pageable;

    /**
     * @return Pageable
     */
    public function previousPageable() : ?Pageable;

    /**
     * @return Traversable
     */
    public function getContent() : Traversable;

    /**
     * @return boolean
     */
    public function hasContent() : bool;

    /**
     * @return Sort
     */
    public function getSort() : ?Sort;
}
