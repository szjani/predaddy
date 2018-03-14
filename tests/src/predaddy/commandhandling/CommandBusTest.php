<?php
declare(strict_types=1);

namespace predaddy\commandhandling;

use Exception;
use PHPUnit\Framework\TestCase;
use predaddy\messagehandling\annotation\AnnotatedMessageHandlerDescriptorFactory;

class CommandBusTest extends TestCase
{
    /**
     * @var CommandBus
     */
    private $commandBus;

    public function setUp()
    {
        $this->commandBus = new CommandBus();
    }

    public function testExactCommandType()
    {
        $called = false;
        $this->commandBus->registerClosure(
            function (Command $command) use (&$called) {
                $called = true;
            }
        );
        $this->commandBus->post(new SimpleCommand());
        self::assertFalse($called);
    }

    /**
     * @test
     * @expectedException \RuntimeException
     */
    public function multipleCommandHandlerMustCauseError()
    {
        $this->commandBus->registerClosure(
            function (SimpleCommand $command) {
                throw new Exception("Should not be thrown");
            }
        );
        $this->commandBus->registerClosure(
            function (SimpleCommand $command) {
                throw new Exception("Should not be thrown");
            }
        );
        $this->commandBus->post(new SimpleCommand());
    }
}
