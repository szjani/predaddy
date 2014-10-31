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
     * @param MethodConfiguration... $configurations on or more instances
     * @return $this
     */
    public function withMethod($class, MethodConfiguration $configurations)
    {
        $configurations = array_slice(func_get_args(), 1);
        $class = trim($class, '\\');
        if (array_key_exists($class, $this->configMap)) {
            $configurations = array_merge($this->configMap[$class], $configurations);
        }
        $this->configMap[$class] = $configurations;
        return $this;
    }

    /**
     * @return array
     */
    public function getConfigMap()
    {
        return $this->configMap;
    }

    /**
     * @return Configuration
     */
    public function build()
    {
        return new Configuration($this);
    }
}
