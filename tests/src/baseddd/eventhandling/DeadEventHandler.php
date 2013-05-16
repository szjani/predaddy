<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

namespace baseddd\eventhandling;

/**
 * Description of DeadEventHandler
 *
 * @author Szurovecz János <szjani@szjani.hu>
 */
interface DeadEventHandler extends EventHandler
{
    public function handle(DeadEvent $event);
}
