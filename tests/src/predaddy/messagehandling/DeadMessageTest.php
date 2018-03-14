<?php
declare(strict_types=1);

namespace predaddy\messagehandling;

use PHPUnit\Framework\TestCase;
use precore\util\UUID;

/**
 * @package predaddy\messagehandling
 *
 * @author Janos Szurovecz <szjani@szjani.hu>
 */
class DeadMessageTest extends TestCase
{
    public function testToString()
    {
        $message = UUID::randomUUID();
        $deadMessage = new DeadMessage($message);
        self::assertTrue(strpos($deadMessage->toString(), $message->toString()) !== false);
    }
}
