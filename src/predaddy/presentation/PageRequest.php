<?php
declare(strict_types=1);

namespace predaddy\presentation;

use InvalidArgumentException;
use precore\lang\BaseObject;
use precore\lang\ObjectInterface;
use precore\util\Objects;
use precore\util\Preconditions;

/**
 * Default implementation of Pageable.
 * The first page number is 0.
 *
 * @author Janos Szurovecz <szjani@szjani.hu>
 */
class PageRequest extends BaseObject implements Pageable
{
    private $page;
    private $size;
    private $sort;

    /**
     * @param int $page Started from 0
     * @param int $size Size of the page
     * @param Sort $sort
     * @throws InvalidArgumentException if $page is < 0
     */
    public function __construct(int $page, int $size, Sort $sort = null)
    {
        Preconditions::checkArgument(0 <= $page, 'Page must be at least 0');
        $this->page = (int) $page;
        $this->size = (int) $size;
        $this->sort = $sort;
    }

    /**
     * @return Pageable
     */
    public function first() : Pageable
    {
        return new PageRequest(0, $this->size, $this->sort);
    }

    /**
     * @return int
     */
    public function getOffset() : int
    {
        return $this->page * $this->size;
    }

    /**
     * @return int
     */
    public function getPageNumber() : int
    {
        return $this->page;
    }

    /**
     * @return int
     */
    public function getPageSize() : int
    {
        return $this->size;
    }

    /**
     * @return Sort
     */
    public function getSort() : Sort
    {
        return $this->sort;
    }

    public function hasPrevious() : bool
    {
        return 0 < $this->page;
    }

    /**
     * @return Pageable
     */
    public function next() : Pageable
    {
        return new PageRequest($this->page + 1, $this->size, $this->sort);
    }

    /**
     * @return Pageable
     */
    public function previousOrFirst() : Pageable
    {
        return $this->hasPrevious()
            ? new PageRequest($this->page - 1, $this->size, $this->sort)
            : $this;
    }

    public function equals(ObjectInterface $object = null) : bool
    {
        if ($object === $this) {
            return true;
        }
        /* @var $object PageRequest */
        return $object !== null
            && $this->getClassName() === $object->getClassName()
            && Objects::equal($this->page, $object->page)
            && Objects::equal($this->size, $object->size)
            && Objects::equal($this->sort, $object->sort);
    }

    public function toString() : string
    {
        return Objects::toStringHelper($this)
            ->add('page', $this->page)
            ->add('size', $this->size)
            ->add('sort', $this->sort)
            ->toString();
    }
}
