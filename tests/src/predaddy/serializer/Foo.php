<?php
declare(strict_types=1);

namespace predaddy\serializer;

use precore\lang\BaseObject;

class Foo extends BaseObject
{
    private $fooPrivate;
    protected $fooProtected;
    public $fooPublic;

    public function __construct()
    {
        $this->fooPrivate = '001fooPrivate';
        $this->fooProtected = '002fooProtected';
        $this->fooPublic = '003fooPublic';
    }

    /**
     * @return mixed
     */
    public function getFooPrivate()
    {
        return $this->fooPrivate;
    }

    /**
     * @return mixed
     */
    public function getFooProtected()
    {
        return $this->fooProtected;
    }

    /**
     * @return mixed
     */
    public function getFooPublic()
    {
        return $this->fooPublic;
    }
}
