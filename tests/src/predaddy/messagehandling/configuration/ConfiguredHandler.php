<?php
declare(strict_types=1);

namespace predaddy\messagehandling\configuration;

use precore\lang\ObjectInterface;
use predaddy\messagehandling\AbstractMessageHandler;

/**
 * Class ConfiguredHandler
 *
 * @package predaddy\messagehandling\configuration
 * @author Janos Szurovecz <szjani@szjani.hu>
 */
class ConfiguredHandler extends AbstractMessageHandler
{
    public $messageOverConfiguredHandler;

    /**
     * @param ObjectInterface $objectInterface
     * @return ObjectInterface
     */
    public function configuredHandler(ObjectInterface $objectInterface)
    {
        $this->messageOverConfiguredHandler = $objectInterface;
        return $objectInterface;
    }
}
