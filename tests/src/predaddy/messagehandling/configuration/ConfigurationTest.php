<?php

namespace predaddy\messagehandling\configuration;

use PHPUnit_Framework_TestCase;
use predaddy\messagehandling\MessageBus;
use stdClass;

/**
 * Class ConfigurationTest
 *
 * @package predaddy\messagehandling\configuration
 * @author Janos Szurovecz <szjani@szjani.hu>
 */
class ConfigurationTest extends PHPUnit_Framework_TestCase
{
    const A_METHOD_NAME = 'methodName';
    const A_METHOD_NAME2 = 'methodName2';
    const A_PRIORITY = 2;
    const A_CLASS_NAME = __CLASS__;

    private $anObject;

    protected function setUp()
    {
        $this->anObject = $this;
    }

    /**
     * @test
     */
    public function shouldReturnNoMethods()
    {
        $configuration = Configuration::builder()->build();
        self::assertCount(0, $configuration->methodsFor($this->anObject));
    }

    /**
     * @test
     */
    public function shouldBuildOneMethod()
    {
        $configuration = Configuration::builder()
            ->withMethod(self::A_CLASS_NAME, new MethodConfiguration(self::A_METHOD_NAME, self::A_PRIORITY))
            ->build();
        $methodConfigs = $configuration->methodsFor($this->anObject);
        self::assertCount(1, $methodConfigs);
        self::assertEquals(self::A_METHOD_NAME, $methodConfigs[0]->getName());
        self::assertEquals(self::A_PRIORITY, $methodConfigs[0]->getPriority());
    }

    /**
     * @test
     */
    public function shouldBuildTwoMethods()
    {
        $configuration = Configuration::builder()
            ->withMethod(self::A_CLASS_NAME, new MethodConfiguration(self::A_METHOD_NAME, self::A_PRIORITY))
            ->withMethod(self::A_CLASS_NAME, new MethodConfiguration(self::A_METHOD_NAME2))
            ->build();
        $methodConfigs = $configuration->methodsFor($this->anObject);
        self::assertCount(2, $methodConfigs);
        self::assertEquals(self::A_METHOD_NAME2, $methodConfigs[1]->getName());
        self::assertEquals(MessageBus::DEFAULT_PRIORITY, $methodConfigs[1]->getPriority());
    }

    /**
     * @test
     */
    public function shouldBuildTwoMethodsWithOneLine()
    {
        $configuration = Configuration::builder()
            ->withMethod(
                self::A_CLASS_NAME,
                new MethodConfiguration(self::A_METHOD_NAME, self::A_PRIORITY),
                new MethodConfiguration(self::A_METHOD_NAME2)
            )
            ->build();
        $methodConfigs = $configuration->methodsFor($this->anObject);
        self::assertCount(2, $methodConfigs);
    }
}
