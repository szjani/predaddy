<?php
/*
 * Copyright (c) 2012-2014 Janos Szurovecz
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

namespace predaddy\commandhandling;

use precore\util\Preconditions;
use predaddy\domain\Repository;
use predaddy\messagehandling\MessageHandlerDescriptorFactory;
use predaddy\messagehandling\SubscriberExceptionHandler;

/**
 * DirectCommandBus automatically registers a DirectCommandForwarder object as a handler
 * which handles all unhandled commands. This bus should be used if business method parameters
 * in the aggregates are Command objects.
 *
 * If you need to handle a particular command explicit, you can register your own command handler.
 * In this case the command is not being dispatched to the registered DirectCommandForwarder object
 * but you have to manage that aggregate instead.
 *
 * If you have specialized repositories for your aggregates, it is recommended to use RepositoryDelegate.
 *
 * @author Janos Szurovecz <szjani@szjani.hu>
 */
class DirectCommandBus extends CommandBus
{
    /**
     * @param DirectCommandBusBuilder $builder
     */
    public function __construct(DirectCommandBusBuilder $builder)
    {
        parent::__construct($builder);
        $this->register(new DirectCommandForwarder($builder->getRepository(), $builder->getHandlerDescriptorFactory()));
    }

    /**
     * The given repository cannot be null, the default value is due to PHP restrictions.
     *
     * @param Repository $repository Is being passed to the registered DirectCommandForwarder
     * @return DirectCommandBusBuilder
     */
    public static function builder(Repository $repository = null)
    {
        return new DirectCommandBusBuilder(Preconditions::checkNotNull($repository));
    }
}
