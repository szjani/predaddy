<?php
declare(strict_types=1);

namespace predaddy\commandhandling;

use PHPUnit\Framework\TestCase;
use precore\util\UUID;
use predaddy\messagehandling\ClosureWrapper;

/**
 * @package predaddy\commandhandling
 *
 * @author Janos Szurovecz <szjani@szjani.hu>
 */
class CommandFunctionDescriptorTest extends TestCase
{
    const ANY_PRIORITY = 0;

    /**
     * @test
     */
    public function shouldInvalidIfParamTypeIsNotCommand()
    {
        $function = function (UUID $uuid) {};
        $descriptor = new CommandFunctionDescriptor(new ClosureWrapper($function), self::ANY_PRIORITY);
        self::assertFalse($descriptor->isValid());
    }
}
