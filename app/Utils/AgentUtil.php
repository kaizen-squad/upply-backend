<?php

namespace App\Utils;

use Jenssegers\Agent\Agent;

class AgentUtil {
    
    public static function getDeviceName(){

    $agent = new Agent();

    $deviceName = $agent->device() .  '_on_'. $agent->browser() . '_'. $agent->platform() ;

    return $deviceName;

    }
}