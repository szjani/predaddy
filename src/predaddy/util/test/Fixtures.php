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

namespace predaddy\util\test;

use precore\lang\ObjectClass;
use precore\util\Preconditions;
use predaddy\domain\Repository;

/**
 * Utility class which makes easier to test aggregates. Supports both ES and non-ES aggregates.
 * Follows BDD concept:
 *  - init commands or events initialize the AR
 *  - the required command should be sent
 *  - the expected events, return values, or thrown exception can be checked
 *
 * {@link Fixture} helps to fill commands with aggregate ID, and ignore some properties when compares events.
 * It is convenience if the aggregate ID is generated inside the AR, and further commands need to know it.
 * This feature works only with {@link AbstractCommand} and {@link AbstractDomainEvent} objects.
 *
 * @beta This, and all related test utility classes are under development and can be modified anytime.
 * @package predaddy\util
 * @author Janos Szurovecz <szjani@szjani.hu>
 */
final class Fixtures
{
    private function __construct()
    {
    }

    /**
     * @param string $aggregateClass which should be tested
     * @param Repository $repository for DirectCommandBus only in non-ES case
     * @return Fixture
     */
    public static function newGivenWhenThenFixture($aggregateClass, Repository $repository = null)
    {
        $arClassObject = ObjectClass::forName($aggregateClass);
        Preconditions::checkArgument(
            ObjectClass::forName('\predaddy\domain\AggregateRoot')->isAssignableFrom($arClassObject),
            'Aggregate class [%s] does not implement AggregateRoot interface',
            $aggregateClass
        );
        $eventSourcedClass = ObjectClass::forName('\predaddy\domain\eventsourcing\EventSourcedAggregateRoot');
        return $eventSourcedClass->isAssignableFrom($arClassObject)
            ? new EventSourcedFixture($aggregateClass)
            : new CommandSourcedFixture($aggregateClass, $repository);
    }
}
