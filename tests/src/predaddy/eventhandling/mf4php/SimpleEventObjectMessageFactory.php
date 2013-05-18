<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

namespace predaddy\eventhandling\mf4php;

use predaddy\eventhandling\SimpleEvent;

/**
 * Description of SimpleEventObjectMessageFactory
 *
 * @author Szurovecz János <szjani@szjani.hu>
 */
abstract class SimpleEventObjectMessageFactory implements ObjectMessageFactory
{
    public static function getEventClass()
    {
        return SimpleEvent::className();
    }
}
