<?php
declare(strict_types=1);

namespace predaddy\messagehandling\interceptors;

use PHPUnit\Framework\TestCase;
use precore\util\UUID;
use predaddy\messagehandling\MessageBusObjectMother;
use predaddy\messagehandling\SubscriberExceptionContext;
use RuntimeException;

/**
 * @package predaddy\messagehandling\interceptors
 *
 * @author Janos Szurovecz <szjani@szjani.hu>
 */
class BlockerInterceptorManagerTest extends TestCase
{
    /**
     * @var BlockerInterceptorManager
     */
    private $blockerManager;

    /**
     * @var BlockerInterceptor
     */
    private $blocker;

    /**
     * @var InterceptorChainSpy
     */
    private $mainChain;

    /**
     * @var InterceptorChainSpy
     */
    private $managedChain;

    private $message;

    protected function setUp()
    {
        $this->message = UUID::randomUUID();
        $this->mainChain = $this->getMockBuilder('\predaddy\messagehandling\InterceptorChain')->getMock();
        $this->managedChain = new InterceptorChainSpy();
        $this->blocker = new BlockerInterceptor();
        $this->blockerManager = new BlockerInterceptorManager($this->blocker);
    }

    /**
     * @test
     */
    public function allChainsAreProceed()
    {
        $this->mainChain
            ->expects(self::once())
            ->method('proceed')
            ->will(
                self::returnCallback(
                    function () {
                        $this->blocker->invoke($this->message, $this->managedChain);
                    }
                )
            );
        $this->blockerManager->invoke($this->message, $this->mainChain);
        self::assertTrue($this->managedChain->calledTimes(1));
    }

    /**
     * @test
     */
    public function chainsMustBeClearedIfExceptionOccur()
    {
        $bus = MessageBusObjectMother::createAnnotatedBus();
        $wrapper = $this->getMockBuilder('\predaddy\messagehandling\CallableWrapper')->getMock();
        $context = new SubscriberExceptionContext($bus, $this->message, $wrapper);
        $this->mainChain
            ->expects(self::once())
            ->method('proceed')
            ->will(
                self::returnCallback(
                    function () use ($context) {
                        $this->blocker->invoke($this->message, $this->managedChain);
                        $this->blockerManager->handleException(
                            new RuntimeException('Expected exception'),
                            $context
                        );
                    }
                )
            );
        $this->blockerManager->invoke($this->message, $this->mainChain);
        self::assertTrue($this->managedChain->calledTimes(0));
    }
}
