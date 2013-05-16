<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

namespace baseddd\eventhandling;

/**
 * Description of SimpleEventHandler
 *
 * @author Szurovecz János <szjani@szjani.hu>
 */
interface SimpleEventHandler extends EventHandler
{
    public function handle(SimpleEvent $event);
}
