<?php
declare(strict_types=1);

namespace sample;

/**
 * Although this example works, you should use a real TransactionManager implementation
 * instead of NOPTransactionManager.
 *
 * @see https://github.com/szjani/trf4php-doctrine
 * @see https://github.com/doctrine/doctrine2
 */

use Exception;
use predaddy\messagehandling\AbstractMessage;
use predaddy\messagehandling\interceptors\WrapInTransactionInterceptor;
use predaddy\messagehandling\Message;
use predaddy\messagehandling\SimpleMessageBus;
use trf4php\NOPTransactionManager;

require_once __DIR__ . '/../../bootstrap.php';

class SimpleMessage extends AbstractMessage
{
}

/**
 * Initialization
 */
$trInterceptor = new WrapInTransactionInterceptor(new NOPTransactionManager());
$bus = SimpleMessageBus::builder()
    ->withInterceptors([$trInterceptor])
    ->withExceptionHandler($trInterceptor)
    ->build();

/**
 * Code from a service class.
 */
$bus->registerClosure(
    function (Message $message) {
        throw new Exception('Database error');
    }
);
$bus->post(new SimpleMessage());
