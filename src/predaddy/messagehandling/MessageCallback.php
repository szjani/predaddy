<?php
declare(strict_types=1);

namespace predaddy\messagehandling;

use Exception;

/**
 * A MessageCallback instance can be passed to MessageBus::post() method as the second argument
 * in order to be notified about message handlers' behaviour.
 *
 * If a message handler has a return value, that will be passed to the onSuccess() method.
 * When a message handler throws any Exception, onFailure() method will be called with that.
 *
 * @author Janos Szurovecz <szjani@szjani.hu>
 */
interface MessageCallback
{
    /**
     * @param mixed $result
     * @return void
     */
    public function onSuccess($result) : void;

    /**
     * @param Exception $exception
     * @return void
     */
    public function onFailure(Exception $exception) : void;
}
