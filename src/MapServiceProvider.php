<?php

namespace j552121229l\laravelAdminExtMap;

use Encore\Admin\Form;
use Illuminate\Support\ServiceProvider;

class MapServiceProvider extends ServiceProvider
{
    /**
     * {@inheritdoc}
     */
    public function boot(StarRatingExtension $extension)
    {
        Form::extend('map', Map::class);
    }
}
