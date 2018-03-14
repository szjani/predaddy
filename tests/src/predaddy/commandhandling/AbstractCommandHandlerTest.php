<?php
declare(strict_types=1);

namespace predaddy\commandhandling;

use PHPUnit\Framework\TestCase;

class AbstractCommandHandlerTest extends TestCase
{
    public function testGetRepository()
    {
        $repository = $this->getMockBuilder('\predaddy\domain\Repository')->getMock();
        $handler = $this->getMockForAbstractClass(__NAMESPACE__ . '\AbstractCommandHandler', [$repository]);
        self::assertSame($repository, $handler->getRepository());
    }
}
