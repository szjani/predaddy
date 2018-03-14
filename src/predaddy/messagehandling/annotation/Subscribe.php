<?php
declare(strict_types=1);

namespace predaddy\messagehandling\annotation;

use predaddy\messagehandling\MessageBus;

/**
 * Mark handle methods in your EventHandler classes with this annotation.
 *
 * @Annotation
 * @Target("METHOD")
 *
 * @author Janos Szurovecz <szjani@szjani.hu>
 */
final class Subscribe
{
    /**
     * @var integer
     */
    public $priority = MessageBus::DEFAULT_PRIORITY;
}
