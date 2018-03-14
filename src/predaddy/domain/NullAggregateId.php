<?php
declare(strict_types=1);

namespace predaddy\domain;

use precore\lang\BaseObject;
use precore\util\Objects;

/**
 * It probably does not need to be used in application level.
 *
 * @see \predaddy\domain\AbstractDomainEvent
 * @author Janos Szurovecz <szjani@szjani.hu>
 */
final class NullAggregateId extends BaseObject implements AggregateId
{
    private static $instance;

    /**
     * @return NullAggregateId
     */
    public static function instance() : NullAggregateId
    {
        return self::$instance;
    }

    /**
     * Should not be called!
     */
    public static function init() : void
    {
        self::$instance = new NullAggregateId();
    }

    private function __construct()
    {
    }

    /**
     * @return string
     */
    public function value() : string
    {
        return '';
    }

    /**
     * @return string
     */
    public function aggregateClass() : string
    {
        return '';
    }

    public function toString() : string
    {
        return Objects::toStringHelper(__CLASS__)->toString();
    }
}
NullAggregateId::init();
