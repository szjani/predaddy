<?php
declare(strict_types=1);

namespace predaddy\presentation;

use precore\lang\ObjectInterface;

/**
 * Useful for pagination to be able to define paging and sorting informations.
 *
 * @author Janos Szurovecz <szjani@szjani.hu>
 */
interface Pageable extends ObjectInterface
{
    /**
     * @return int
     */
    public function getPageNumber() : int;

    /**
     * @return int
     */
    public function getPageSize() : int;

    /**
     * @return int
     */
    public function getOffset() : int;

    /**
     * @return Sort
     */
    public function getSort() : Sort;

    /**
     * @return Pageable
     */
    public function next() : Pageable;

    /**
     * @return Pageable
     */
    public function previousOrFirst() : Pageable;

    /**
     * @return Pageable
     */
    public function first() : Pageable;

    /**
     * @return boolean
     */
    public function hasPrevious() : bool;
}
