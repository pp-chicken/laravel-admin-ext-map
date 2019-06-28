<?php
/**
 * Map.php created by JI.
 * Author: psyche
 * Date: 2019-06-27
 * Time: 17:53
 */

namespace 552121229l\laravel-admin-ext-map;

class Map extends \Encore\Admin\Form\Field\Map
{
    public function __construct($column, $arguments)
    {
        $this->column['lat'] = (string) $column;
        $this->column['lng'] = (string) $arguments[0];

        array_shift($arguments);

        $this->label = $this->formatLabel($arguments);
        $this->id = $this->formatId($this->column);

        /*
         * Google map is blocked in mainland China
         * people in China can use Tencent map instead(;
         */
        switch (config('admin.map_provider')) {
            case 'amap':
                $this->useAmap();
                break;
            case 'tencent':
                $this->useTencentMap();
                break;
            case 'google':
                $this->useGoogleMap();
                break;
            case 'yandex':
                $this->useYandexMap();
                break;
            default:
                $this->useAmap();
        }
    }

    /**
     * Get assets required by this field.
     *
     * @return array
     */
    public static function getAssets()
    {
        switch (config('admin.map_provider')) {
            case 'amap':
                $js = '//webapi.amap.com/maps?v=1.4.14&key='. env('AMAP_API_KEY');
                break;
            case 'tencent':
                $js = '//map.qq.com/api/js?v=2.exp';
                break;
            case 'google':
                $js = '//maps.googleapis.com/maps/api/js?v=3.exp&sensor=false&key='.env('GOOGLE_API_KEY'). '&libraries=places';
                break;
            case 'yandex':
                $js = '//api-maps.yandex.ru/2.1/?lang=ru_RU';
                break;
            default:
                $js = '//maps.googleapis.com/maps/api/js?v=3.exp&sensor=false&key='.env('GOOGLE_API_KEY');
        }

        return compact('js');
    }

    public function useGoogleMap()
    {
        $this->script = <<<EOT
        var map, map_center, infoWindow, marker;
        (function() {
            function initGoogleMap(name) {
                var lat = $('#{$this->id['lat']}');
                var lng = $('#{$this->id['lng']}');
                
                var container = document.getElementById("map_"+name);
                infoWindow = new google.maps.InfoWindow();
                
                if (lat.val() && lng.val()) {
                    map_center = new google.maps.LatLng(lat.val(), lng.val());
                    initMap();
                } else {
                    if (navigator.geolocation) {
                        navigator.geolocation.getCurrentPosition(function(position) {
                            map_center = new google.maps.LatLng(
                                position.coords.latitude,
                                position.coords.longitude);
                            
                            infoWindow.setPosition(map_center);
                            initMap();
                            if (mapSearch !== undefined)
                                mapSearch();
                        }, function() {
                          handleLocationError(true, infoWindow, map.getCenter());
                        });
                      } else {
                        // Browser doesn't support Geolocation
                        handleLocationError(false, infoWindow, map.getCenter());
                      }
                }
                
                function initMap(){
                    var options = {
                        zoom: 13,
                        center: map_center,
                        panControl: false,
                        zoomControl: true,
                        scaleControl: true,
                        mapTypeId: google.maps.MapTypeId.ROADMAP
                    }
                    map = new google.maps.Map(container, options);
                    map.addListener('click', function(event) {
                        marker.setPosition(event.latLng);
                    });
                    map.addListener('center_changed', function() {
                        map_center = this.getCenter();
                    });
                    newMarker();
                }
                
                function newMarker(){
                    marker = new google.maps.Marker({
                        position: map_center,
                        map: map,
                        animation: google.maps.Animation.BOUNCE
                    });
                    marker.addListener('position_changed', function () {
                        var latLng = this.getPosition();
                        lat.val(latLng.lat());
                        lng.val(latLng.lng());
                    });
                }
                
                function handleLocationError(browserHasGeolocation, infoWindow, pos) {
                    infoWindow.setPosition(pos);
                    infoWindow.setContent(browserHasGeolocation ?
                        'Error: The Geolocation service failed.' :
                        'Error: Your browser doesn\'t support geolocation.');
                    infoWindow.open(map);
                }
            }
            initGoogleMap('{$this->id['lat']}{$this->id['lng']}');
        })();
        
EOT;
    }

    public function useTencentMap()
    {
        $this->script = <<<EOT
        (function() {
            function initTencentMap(name) {
                var lat = $('#{$this->id['lat']}');
                var lng = $('#{$this->id['lng']}');
    
                var center = new qq.maps.LatLng(lat.val(), lng.val());
    
                var container = document.getElementById("map_"+name);
                var map = new qq.maps.Map(container, {
                    center: center,
                    zoom: 13
                });
    
                var marker = new qq.maps.Marker({
                    position: center,
                    draggable: true,
                    map: map
                });
    
                if( ! lat.val() || ! lng.val()) {
                    var citylocation = new qq.maps.CityService({
                        complete : function(result){
                            map.setCenter(result.detail.latLng);
                            marker.setPosition(result.detail.latLng);
                        }
                    });
    
                    citylocation.searchLocalCity();
                }
    
                qq.maps.event.addListener(map, 'click', function(event) {
                    marker.setPosition(event.latLng);
                });
    
                qq.maps.event.addListener(marker, 'position_changed', function(event) {
                    var position = marker.getPosition();
                    lat.val(position.getLat());
                    lng.val(position.getLng());
                });
            }
    
            initTencentMap('{$this->id['lat']}{$this->id['lng']}');
        })();
EOT;
    }

    public function useAmap()
    {
        $this->script = <<<EOT
            var map = {};
            var lat, lng, map_center, map_click = false;
            (function(map_id) {
                //获取坐标
                lat = $('#{$this->id['lat']}');
                lng = $('#{$this->id['lng']}');

                //初始化地图
                map = new AMap.Map(map_id, {
                    zoom: 13
                });

                //初始化中心点
                if(lat.val() &&lng.val()) {
                    map_center = new AMap.LngLat(lat.val(), lng.val());
                    map.setCenter(map_center);
                } else {
                    var citylocation = {};
                    map_center = map.getCenter();
                    map_click = true;
                    map.plugin('AMap.Geolocation', function () {
                       citylocation = new AMap.Geolocation({
                           enableHighAccuracy: true,//是否使用高精度定位，默认:true
                           timeout: 10000,          //超过10秒后停止定位，默认：无穷大
                           maximumAge: 0,           //定位结果缓存0毫秒，默认：0
                           convert: true,           //自动偏移坐标，偏移后的坐标为高德坐标，默认：true
                           showButton: false,        //显示定位按钮，默认：true
                           // buttonPosition: 'LB',    //定位按钮停靠位置，默认：'LB'，左下角
                           buttonOffset: new AMap.Pixel(10, 20),//定位按钮与设置的停靠位置的偏移量，默认：Pixel(10, 20)
                           showMarker: false,        //定位成功后在定位到的位置显示点标记，默认：true
                           showCircle: false,        //定位成功后用圆圈表示定位精度范围，默认：true
                           panToLocation: true,     //定位成功后将定位到的位置作为地图中心点，默认：true
                           zoomToAccuracy:true      //定位成功后调整地图视野范围使定位位置及精度范围视野内可见，默认：false
                       });
                       map.addControl(citylocation);
                       citylocation.getCurrentPosition();
                       AMap.event.addListener(citylocation, 'complete', function(CitylocationResult){
                           marker.setPosition(CitylocationResult.position);
                       });//返回定位信息
                       AMap.event.addListener(citylocation, 'error', function(error){
                           console.log(error); //返回定位出错信息
                       });
                   });
                }

                //初始化marker标记
                var marker = new AMap.Marker({
                    position: map_center,
                    draggable: true,
                    map: map
                });

                //如果master_marker不为空，则暴露给全局master_marker
                if(typeof(master_marker) !== 'undefined') {
                    master_marker = marker;
                }

                AMap.event.addListener(map, 'click', function(event) {
                    map_click = true;
                    map_center = event.lnglat;
                    marker.setPosition(map_center);
                    map.setCenter(map_center);
                });

                AMap.event.addListener(map, 'moveend', function(event) {
                    map_center = map.getCenter();
                    lat.val(map_center.lat);
                    lng.val(map_center.lng);
                });

                //取消难看的高德地图图标
                $('.amap-copyright').remove();
                $('.amap-logo').remove();

            })("map_"+'{$this->id['lat']}{$this->id['lng']}');
EOT;
    }
}