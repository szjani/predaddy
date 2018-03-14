<?php
declare(strict_types=1);

namespace predaddy\messagehandling\configuration;

use predaddy\messagehandling\MessageBus;

/**
 * Builder for {@link Configuration} class.
 *
 * @package predaddy\messagehandling\configuration
 * @author Janos Szurovecz <szjani@szjani.hu>
 */
final class ConfigurationBuilder
{
    private $configMap = [];

    public function __construct()
    {
    }

    /**
     * @param string $class FQCN
     * @param string $methodName
     * @param int $priority
     * @return $this
     */
    public function withMethod(string $class, string $methodName, int $priority = MessageBus::DEFAULT_PRIORITY) : ConfigurationBuilder
    {
        $this->configMap[trim($class, '\\')][] = new MethodConfiguration($methodName, $priority);
        return $this;
    }

    /**
     * @return array
     */
    public function getConfigMap() : array
    {
        return $this->configMap;
    }

    /**
     * @return Configuration
     */
    public function build() : Configuration
    {
        return new Configuration($this);
    }
}
