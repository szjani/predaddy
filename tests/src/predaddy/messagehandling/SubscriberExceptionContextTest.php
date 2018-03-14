<?php
declare(strict_types=1);

namespace predaddy\messagehandling;

use PHPUnit\Framework\TestCase;
use ReflectionMethod;

/**
 * @package predaddy\messagehandling
 *
 * @author Janos Szurovecz <szjani@szjani.hu>
 */
class SubscriberExceptionContextTest extends TestCase
{
    public function testGetters()
    {
        $bus = $this->getMockBuilder('predaddy\messagehandling\MessageBus')->getMock();
        $message = $this;
        $wrapper = new MethodWrapper($this, new ReflectionMethod(SimpleMessage::className(), 'toString'));
        $context = new SubscriberExceptionContext($bus, $message, $wrapper);
        self::assertSame($bus, $context->getMessageBus());
        self::assertSame($message, $context->getMessage());
        self::assertSame($wrapper, $context->getCallableWrapper());
    }
}
