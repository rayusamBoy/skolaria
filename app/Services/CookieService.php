<?php

namespace App\Services;

use Illuminate\Support\Facades\Cookie;

class CookieService
{
    public function set($name, $value, $minutes = 1440, ...$options)
    {
        return Cookie::make($name, $value, $minutes, ...$options);
    }

    public function setForever($name, $value, ...$options)
    {
        return Cookie::forever($name, $value, ...$options);
    }

    public function get($name)
    {
        return Cookie::get($name);
    }

    public function has($name)
    {
        return Cookie::has($name);
    }

    public function delete($name)
    {
        return Cookie::forget($name);
    }
}
