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

namespace src\predaddy\serializer;

use PHPUnit_Framework_TestCase;
use predaddy\serializer\Bar;
use predaddy\serializer\PHPSerializer;

/**
 * @package src\predaddy\serializer
 *
 * @author Szurovecz János <szjani@szjani.hu>
 */
class PHPSerializerTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var PHPSerializer
     */
    private $serializer;

    protected function setUp()
    {
        $this->serializer = new PHPSerializer();
    }

    /**
     * @test
     */
    public function deserializationShouldWork()
    {
        $bar = new Bar();
        $serialized = $this->serializer->serialize($bar);
        self::assertEquals($bar, $this->serializer->deserialize($serialized, Bar::objectClass()));
    }
}
