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

namespace predaddy\domain\impl;

use InvalidArgumentException;
use precore\lang\ObjectClass;
use predaddy\domain\Repository;
use predaddy\domain\RepositoryRepository;

/**
 * @package predaddy\domain\impl
 *
 * @author Szurovecz János <szjani@szjani.hu>
 */
class RegisterableRepositoryRepository implements RepositoryRepository
{
    private $repositories = [];

    /**
     * @param $aggregateClass
     * @param Repository $repository
     */
    public function register($aggregateClass, Repository $repository)
    {
        $this->repositories[$aggregateClass] = $repository;
    }

    /**
     * @param $aggregateClass
     */
    public function unregister($aggregateClass)
    {
        unset($this->repositories[$aggregateClass]);
    }

    /**
     * @param string $aggregateClass
     * @throws InvalidArgumentException If there is no repository registered for the given aggregate
     * @return Repository
     */
    public function getRepository($aggregateClass)
    {
        if (!array_key_exists($aggregateClass, $this->repositories)) {
            throw new InvalidArgumentException("No registered repository for class " . $aggregateClass);
        }
        return $this->repositories[$aggregateClass];
    }
}
