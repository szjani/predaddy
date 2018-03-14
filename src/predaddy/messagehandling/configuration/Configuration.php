<?php
declare(strict_types=1);

namespace predaddy\messagehandling\configuration;

use precore\lang\ObjectClass;

/**
 * A {@link Configuration} object stores handler method definitions for handler classes.
 * It is intended to configure {@link SimpleMessageBus} without annotation scanning.
 *
 * Supports parent classes as expected. It means you need to register a handler method for only one class
 * and that will be valid for all subclasses.
 *
 * Any configuration file reader classes should use it.
 *
 * <pre>
 *  $config = Configuration::builder()
 *      ->withMethod('foo/Foo', 'foo')
 *      ->withMethod('foo/Bar', 'bar', 2)
 *      ->build();
 * </pre>
 *
 * @see ConfiguredMessageHandlerDescriptorFactory
 * @package predaddy\messagehandling\configuration
 * @author Janos Szurovecz <szjani@szjani.hu>
 */
final class Configuration
{
    private $configMap;

    /**
     * @param ConfigurationBuilder $builder
     */
    public function __construct(ConfigurationBuilder $builder)
    {
        $this->configMap = $builder->getConfigMap();
    }

    /**
     * Returns the registered {@link MethodConfiguration}s for the given handler.
     *
     * @param object $handler
     * @return MethodConfiguration[]
     */
    public function methodsFor($handler) : array
    {
        $handlerClass = get_class($handler);
        if (!array_key_exists($handlerClass, $this->configMap)) {
            $handlerClassObject = ObjectClass::forName($handlerClass);
            $handlerConfig = [];
            foreach ($this->configMap as $class => $currentConfigs) {
                if (ObjectClass::forName($class)->isAssignableFrom($handlerClassObject)) {
                    $handlerConfig = array_merge($handlerConfig, $currentConfigs);
                }
            }
            $this->configMap[$handlerClass] = $handlerConfig;
        }
        return $this->configMap[$handlerClass];
    }

    /**
     * @return ConfigurationBuilder
     */
    public static function builder() : ConfigurationBuilder
    {
        return new ConfigurationBuilder();
    }
}
