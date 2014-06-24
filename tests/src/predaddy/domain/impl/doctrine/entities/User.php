<?php
/*
 * Copyright (c) 2013 Szurovecz János
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

namespace predaddy\domain\impl\doctrine\entities;

use precore\lang\ObjectInterface;
use precore\util\UUID;
use predaddy\domain\AbstractAggregateRoot;
use predaddy\domain\DefaultAggregateId;
use predaddy\domain\UUIDAggregateId;
use predaddy\messagehandling\annotation\Subscribe;
use Doctrine\ORM\Mapping as ORM;

/**
 * Description of User
 *
 * @author Szurovecz János <szjani@szjani.hu>
 * @ORM\Entity
 */
class User extends AbstractAggregateRoot
{
    const DEFAULT_VALUE = 1;

    /**
     * @ORM\Id
     * @ORM\Column(type="string")
     * @var UUIDAggregateId
     */
    private $id;

    /**
     * @ORM\Column(type="integer")
     * @var int
     */
    public $value = self::DEFAULT_VALUE;

    /**
     * @ORM\Version
     * @ORM\Column(type="integer")
     * @var int
     */
    private $version;

    public function __construct()
    {
        $this->id = UUID::randomUUID()->toString();
        $this->raise(new UserCreated());
    }

    public function getId()
    {
        return new DefaultAggregateId($this->id, self::className());
    }

    public function increment()
    {
        $this->value++;
        $this->raise(new IncrementedEvent());
    }
}
