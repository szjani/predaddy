<?php
declare(strict_types=1);

namespace predaddy\presentation;

use ArrayIterator;
use IteratorAggregate;
use precore\lang\BaseObject;
use precore\lang\ObjectInterface;
use precore\util\Objects;

/**
 * Default Page implementation.
 *
 * @author Janos Szurovecz <szjani@szjani.hu>
 */
class PageImpl extends BaseObject implements IteratorAggregate, Page
{
    private $content = [];

    /**
     * @var Pageable
     */
    private $pageable;

    /**
     * @var int
     */
    private $total;

    /**
     * @param array $content
     * @param Pageable $pageable
     * @param int $total Total number of all elements (not just the current page's)
     */
    public function __construct(array $content, Pageable $pageable = null, int $total = null)
    {
        $this->content = array_merge($this->content, $content);
        $this->total = $total === null ? count($content) : $total;
        $this->pageable = $pageable;
    }

    /**
     * @return array of the records in the current page
     */
    public function getContent() : \Traversable
    {
        return new \ArrayObject($this->content);
    }

    /**
     * @return int The page number
     */
    public function getNumber() : int
    {
        return $this->pageable === null
            ? 0
            : $this->pageable->getPageNumber();
    }

    /**
     * @return int size of the content
     */
    public function getSize() : int
    {
        return $this->pageable === null
            ? 0
            : $this->pageable->getPageSize();
    }

    public function getSort() : ?Sort
    {
        return $this->pageable === null
            ? null
            : $this->pageable->getSort();
    }

    public function getTotalElements() : int
    {
        return $this->total;
    }

    public function getTotalPages() : int
    {
        return $this->getSize() == 0
            ? 1
            : (int) ceil($this->total / $this->getSize());
    }

    public function hasContent() : bool
    {
        return count($this->content) !== 0;
    }

    public function hasNextPage() : bool
    {
        return $this->getNumber() + 1 < $this->getTotalPages();
    }

    public function hasPreviousPage() : bool
    {
        return 0 < $this->getNumber();
    }

    public function isFirstPage() : bool
    {
        return !$this->hasPreviousPage();
    }

    public function isLastPage() : bool
    {
        return !$this->hasNextPage();
    }

    /**
     * Create a Pageable object to be able to obtain the next page.
     *
     * @return Pageable
     */
    public function nextPageable() : ?Pageable
    {
        return $this->hasNextPage()
            ? $this->pageable->next()
            : null;
    }

    /**
     * Create a Pageable object to be able to obtain the previous page.
     *
     * @return Pageable
     */
    public function previousPageable() : ?Pageable
    {
        return $this->hasPreviousPage()
            ? $this->pageable->previousOrFirst()
            : null;
    }

    /**
     * @return ArrayIterator|\Traversable
     */
    public function getIterator()
    {
        return new ArrayIterator($this->content);
    }

    public function equals(ObjectInterface $object = null) : bool
    {
        if ($object === $this) {
            return true;
        }
        /* @var $object PageImpl */
        return $object !== null
            && $this->getClassName() === $object->getClassName()
            && Objects::equal($this->total, $object->total)
            && Objects::equal($this->content, $object->content)
            && Objects::equal($this->pageable, $object->pageable);
    }

    public function toString() : string
    {
        return Objects::toStringHelper($this)
            ->add('total', $this->total)
            ->add('pageable', $this->pageable)
            ->toString();
    }
}
