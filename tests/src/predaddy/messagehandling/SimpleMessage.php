<?php
declare(strict_types=1);

namespace predaddy\messagehandling;

/**
 * Description of SimpleMessage
 *
 * @author Janos Szurovecz <szjani@szjani.hu>
 */
class SimpleMessage extends AbstractMessage
{
    public $data = 'hello';
    protected $protectedData;
    private $privateData;

    const PRIVATE_VALUE = 'private';

    public function __construct()
    {
        parent::__construct();
        $this->privateData = self::PRIVATE_VALUE;
        $this->protectedData = 'protected';
    }

    /**
     * @return string
     */
    public function getProtectedData()
    {
        return $this->protectedData;
    }

    /**
     * @return string
     */
    public function getPrivateData()
    {
        return $this->privateData;
    }
}
