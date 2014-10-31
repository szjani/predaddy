<?php
/*
 * Copyright (c) 2014 Janos Szurovecz
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy of
 * this software and associated documentation files (the "Software"), to deal in
 * the Software without restriction, including without limitation the rights to
 * use, copy, modify, merge, publish, distribute, sublicense, and/or sell copies
 * of the Software, and to permit persons to whom the Software is furnished to do
 * so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in all
 * copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
 * SOFTWARE.
 */

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
 *      ->withMethod('foo/Foo', new MethodConfiguration('foo'))
 *      ->withMethod('foo/Bar', new MethodConfiguration('bar', 2))
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
    public function methodsFor($handler)
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
    public static function builder()
    {
        return new ConfigurationBuilder();
    }
}
