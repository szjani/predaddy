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

namespace predaddy\messagehandling;

use ArrayIterator;
use IteratorAggregate;
use precore\lang\Object;
use precore\util\Arrays;
use Traversable;

/**
 * It stores and sorts the FunctionDescriptor objects.
 *
 * @author Janos Szurovecz <szjani@szjani.hu>
 */
final class FunctionDescriptors extends Object implements IteratorAggregate
{
    /**
     * @var FunctionDescriptor[]
     */
    private $descriptors = [];
    private $sorted = true;

    public function add(FunctionDescriptor $descriptor)
    {
        $this->descriptors[] = $descriptor;
        $this->sorted = false;
    }

    public function remove(FunctionDescriptor $descriptor)
    {
        foreach ($this->descriptors as $key => $value) {
            if ($value->equals($descriptor)) {
                unset($this->descriptors[$key]);
                $this->sorted = false;
            }
        }
    }

    /**
     * (PHP 5 &gt;= 5.0.0)<br/>
     * Retrieve an external iterator
     *
     * @link http://php.net/manual/en/iteratoraggregate.getiterator.php
     * @return Traversable An instance of an object implementing <b>Iterator</b> or
     * <b>Traversable</b>
     */
    public function getIterator()
    {
        return new ArrayIterator($this->sortedDescriptors());
    }

    /**
     * @return FunctionDescriptor[]
     */
    private function sortedDescriptors()
    {
        if (!$this->sorted) {
            Arrays::sort($this->descriptors);
            $this->sorted = true;
        }
        return $this->descriptors;
    }
}
