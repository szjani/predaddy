<?php
declare(strict_types=1);

namespace predaddy\serializer;

class Bar extends Foo
{
    private $barPrivate;
    protected $barProtected;
    public $barPublic;

    public function __construct()
    {
        parent::__construct();
        $this->barPrivate = '001barPrivate';
        $this->barProtected = '002barProtected';
        $this->barPublic = '003barPublic';
    }

    /**
     * @return mixed
     */
    public function getBarPrivate()
    {
        return $this->barPrivate;
    }

    /**
     * @return mixed
     */
    public function getBarProtected()
    {
        return $this->barProtected;
    }

    /**
     * @return mixed
     */
    public function getBarPublic()
    {
        return $this->barPublic;
    }
}
