# laravel-admin-ext-map
map extension for laravel-admin.

这是一个laravel-admin的地图增强扩展

## 安装说明
```shell
composer require encore/laravel-admin
```
app/Admin/bootstrap.php
```php
Form::extend('map', \l552121229\laravelAdminExtMap\Map::class);
```
