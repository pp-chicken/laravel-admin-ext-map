<?php

namespace l552121229\laravelAdminExtMap;

use Encore\Admin\Admin;
use Encore\Admin\Form;
use Illuminate\Support\ServiceProvider;
use l552121229\laravelAdminExtMap\Events\ChangeMap;

class MapServiceProvider extends ServiceProvider
{
    protected $defer = false;
    /**
     * {@inheritdoc}
     */
    public function boot(ChangeMap $change)
    {
        Admin::booting(function () use ($change) {
            $change->handle();
        });
    }
}
