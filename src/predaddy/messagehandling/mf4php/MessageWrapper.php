<?php
declare(strict_types=1);

namespace predaddy\messagehandling\mf4php;

use Serializable;

final class MessageWrapper implements Serializable
{
    /**
     * @var mixed
     */
    private $message;

    public function __construct($message)
    {
        $this->message = $message;
    }

    /**
     * (PHP 5 &gt;= 5.1.0)<br/>
     * String representation of object
     *
     * @link http://php.net/manual/en/serializable.serialize.php
     * @return string the string representation of the object or null
     */
    public function serialize()
    {
        return serialize($this->message);
    }

    /**
     * (PHP 5 &gt;= 5.1.0)<br/>
     * Constructs the object
     *
     * @link http://php.net/manual/en/serializable.unserialize.php
     * @param string $serialized <p>
     * The string representation of the object.
     * </p>
     * @return void
     */
    public function unserialize($serialized)
    {
        $this->message = unserialize($serialized);
    }

    /**
     * @return mixed
     */
    public function getMessage()
    {
        return $this->message;
    }
}
