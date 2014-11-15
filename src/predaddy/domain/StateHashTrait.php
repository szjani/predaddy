<?php
/**
 * Created by IntelliJ IDEA.
 * User: szjani
 * Date: 2014.11.15.
 * Time: 15:25
 */

namespace predaddy\domain;


trait StateHashTrait {

    protected $stateHash;

    /**
     * @return null|string
     */
    public function stateHash()
    {
        return $this->stateHash;
    }
}
