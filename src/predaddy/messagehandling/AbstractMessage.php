<?php
/*
 * Copyright (c) 2013 Janos Szurovecz
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
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO message SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
 * SOFTWARE.
 */

namespace predaddy\messagehandling;

use DateTime;
use precore\lang\Object;
use precore\lang\ObjectInterface;
use precore\util\Objects;
use precore\util\UUID;

/**
 * Base class for all types of messages. Contains the message identifier and timestamp.
 *
 * @author Janos Szurovecz <szjani@szjani.hu>
 */
abstract class AbstractMessage extends Object implements Message
{
    protected $id;
    protected $created;

    public function __construct()
    {
        $this->created = new DateTime();
        $this->id = UUID::randomUUID()->toString();
    }

    /**
     * @return string
     */
    public function identifier()
    {
        return $this->id;
    }

    /**
     * @return DateTime
     */
    public function created()
    {
        return clone $this->created;
    }

    final public function equals(ObjectInterface $object = null)
    {
        if ($object === $this) {
            return true;
        }
        /* @var $object AbstractMessage */
        return $object !== null
            && $this->getClassName() === $object->getClassName()
            && $this->id === $object->id;
    }

    /**
     * @return \precore\util\ToStringHelper
     */
    protected function toStringHelper()
    {
        return Objects::toStringHelper($this)
            ->add('id', $this->identifier())
            ->add('created', $this->created());
    }

    public function toString()
    {
        return $this->toStringHelper()->toString();
    }
}
