<?php

namespace l552121229\laravelAdminExtMap;

use Encore\Admin\Admin;
use Encore\Admin\Form;
use Illuminate\Support\ServiceProvider;

class MapServiceProvider extends ServiceProvider
{
    /**
     * {@inheritdoc}
     */
    public function boot()
    {
        Admin::booting(function () {
            Form::extend('map', Map::class);
        });
    }
}
