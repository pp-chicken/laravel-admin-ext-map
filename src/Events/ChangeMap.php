<?php
/**
 * ChangeMap.php created by JI.
 * Author: psyche
 * Date: 2019-06-28
 * Time: 18:03
 */
namespace l552121229\laravelAdminExtMap\Events;

class ChangeMap
{
    public function handle()
    {
        //地图session切换
        $map_provider = session('map_provider', 'amap');
        if (request()->get('map_change') === 'true') {
            switch ($map_provider) {
                case 'amap' :
                    config(['admin.map_provider' => 'google']);
                    session(['map_provider' => 'google']);
                    break;
                case 'google' :
                default :
                    config(['admin.map_provider' => 'amap']);
                    session(['map_provider' => 'amap']);
            }
        } else {
            config(['admin.map_provider' => $map_provider]);
        }
    }
}