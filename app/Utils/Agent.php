<?php

use Jenssegers\Agent\Agent;

function getDeviceName(){

    $agent = new Agent();

    $deviceName = $agent->device() .  '_on_'. $agent->browser() . '_'. $agent->platform() ;

    return $deviceName;

}