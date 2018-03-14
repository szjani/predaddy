<?php
declare(strict_types=1);

namespace predaddy\messagehandling\util;

use Exception;
use predaddy\messagehandling\MessageCallback;

/**
 * Simple {@link MessageCallback} which holds all hold the last result and exception.
 * Always overwrites the existing values!
 *
 * @package predaddy\messagehandling\util
 * @author Janos Szurovecz <szjani@szjani.hu>
 */
final class SimpleMessageCallback implements MessageCallback
{
    /**
     * @var mixed|null
     */
    private $result;

    /**
     * @var Exception|null
     */
    private $exception;

    /**
     * @param mixed $result
     * @return void
     */
    public function onSuccess($result) : void
    {
        $this->result = $result;
    }

    /**
     * @param Exception $exception
     * @return void
     */
    public function onFailure(Exception $exception) : void
    {
        $this->exception = $exception;
    }

    /**
     * @return mixed|null
     */
    public function getResult()
    {
        return $this->result;
    }

    /**
     * @return Exception|null
     */
    public function getException() : ?Exception
    {
        return $this->exception;
    }
}
