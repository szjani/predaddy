<?php
declare(strict_types=1);

namespace predaddy\presentation;

use ArrayIterator;
use Countable;
use IteratorAggregate;
use precore\lang\BaseObject;
use precore\lang\ObjectInterface;
use precore\util\Objects;

/**
 * Several Order instance can be stored in a Sort object.
 * It provides some methods to be able to handle Orders easily.
 *
 * @author Janos Szurovecz <szjani@szjani.hu>
 */
class Sort extends BaseObject implements IteratorAggregate, Countable
{
    private $orders = [];

    /**
     * @param array $orders Array of Order objects
     */
    public function __construct(array $orders)
    {
        $this->orders = $orders;
    }

    /**
     * Factory method to order by several properties with the same direction.
     *
     * @param array $properties
     * @param Direction $direction
     * @return Sort
     */
    public static function create(array $properties, Direction $direction = null) : Sort
    {
        if ($direction === null) {
            $direction = Direction::$ASC;
        }
        $orders = [];
        foreach ($properties as $property) {
            $orders[] = new Order($direction, $property);
        }
        return new self($orders);
    }

    /**
     * Does not modifies the object itself,
     * will return a new instance instead.
     *
     * @param Sort $sort
     * @return Sort
     */
    public function andSort(Sort $sort = null) : Sort
    {
        if ($sort === null) {
            return $this;
        }
        $orders = $this->orders;
        foreach ($sort as $order) {
            $orders[] = $order;
        }
        return new Sort($orders);
    }

    /**
     * Returns the direction defined to the given property.
     *
     * @param string $property
     * @return Order
     */
    public function getOrderFor(string $property) : ?Order
    {
        /* @var $order Order */
        foreach ($this as $order) {
            if ($order->getProperty() == $property) {
                return $order;
            }
        }
        return null;
    }

    public function getIterator()
    {
        return new ArrayIterator($this->orders);
    }

    public function count()
    {
        return count($this->orders);
    }

    public function equals(ObjectInterface $object = null) : bool
    {
        if ($object === $this) {
            return true;
        }
        /* @var $object Sort */
        return $object !== null
            && $this->getClassName() === $object->getClassName()
            && $this->orders === $object->orders;
    }

    public function toString() : string
    {
        return Objects::toStringHelper($this)
            ->add('orders', $this->orders)
            ->toString();
    }
}
