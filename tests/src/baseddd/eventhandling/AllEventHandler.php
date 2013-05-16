<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

namespace baseddd\eventhandling;

/**
 * Description of AllEventHandler
 *
 * @author Szurovecz János <szjani@szjani.hu>
 */
interface AllEventHandler extends EventHandler
{
    public function handle(Event $event);
}
