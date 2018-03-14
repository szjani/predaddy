<?php
declare(strict_types=1);

namespace predaddy\commandhandling;

use precore\util\Preconditions;
use predaddy\messagehandling\SimpleMessageBus;

/**
 * A typical command bus has the following behaviours:
 *  - all command handler methods are wrapped by a unique transaction
 *  - the type of the message must be exactly the same as the parameter in the handler method
 *
 * Only one command handler can process a particular command, otherwise a runtime exception will be thrown.
 *
 * @author Janos Szurovecz <szjani@szjani.hu>
 */
class CommandBus extends SimpleMessageBus
{
    /**
     * @param CommandBusBuilder $builder
     */
    public function __construct(CommandBusBuilder $builder = null)
    {
        if ($builder === null) {
            $builder = self::builder();
        }
        parent::__construct($builder);
    }

    /**
     * @return CommandBusBuilder
     */
    public static function builder()
    {
        return new CommandBusBuilder();
    }

    protected function callableWrappersFor($message) : \ArrayObject
    {
        $wrappers = parent::callableWrappersFor($message);
        Preconditions::checkState(
            count($wrappers) <= 1,
            "More than one command handler is registered for message '%s'",
            get_class($message)
        );
        return $wrappers;
    }
}
