<?php
/*
 * Copyright (c) 2012-2014 Szurovecz János
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy of
 * this software and associated documentation files (the "Software"), to deal in
 * the Software without restriction, including without limitation the rights to
 * use, copy, modify, merge, publish, distribute, sublicense, and/or sell copies
 * of the Software, and to permit persons to whom the Software is furnished to do
 * so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in all
 * copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
 * SOFTWARE.
 */

namespace predaddy\domain;

use InvalidArgumentException;
use precore\lang\Object;
use precore\lang\ObjectClass;

abstract class ClassBasedAggregateRootRepository extends Object implements Repository
{
    /**
     * @var ObjectClass
     */
    private $aggregateRootClass;

    /**
     * Using EventBus instance is recommended.
     *
     * @param ObjectClass $aggregateRootClass
     * @throws InvalidArgumentException If $aggregateRootClass does not extend AggregateRoot
     */
    public function __construct(ObjectClass $aggregateRootClass)
    {
        if (!ObjectClass::forName('predaddy\domain\AggregateRoot')->isAssignableFrom($aggregateRootClass)) {
            throw new InvalidArgumentException(
                sprintf(
                    "The given aggregate root class [%s] must implement AggregateRoot",
                    $aggregateRootClass->getName()
                )
            );
        }
        $this->aggregateRootClass = $aggregateRootClass;
    }

    /**
     * @param AggregateRoot $aggregateRoot
     * @throws InvalidArgumentException If the given $aggregateRoot is not an instance of the handled AggregateRoot
     */
    public function save(AggregateRoot $aggregateRoot)
    {
        $this->aggregateRootClass->cast($aggregateRoot);
    }


    /**
     * @return ObjectClass Class of the handled AggregateRoot
     */
    public function getAggregateRootClass()
    {
        return $this->aggregateRootClass;
    }

    protected function throwInvalidAggregateIdException(AggregateId $aggregateId)
    {
        throw new InvalidArgumentException(
            sprintf(
                "Aggregate [%s] with ID [%s] does not exist",
                $this->getAggregateRootClass()->getName(),
                $aggregateId->getValue()
            )
        );
    }
}
