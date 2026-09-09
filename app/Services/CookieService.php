<?php

namespace App\Services;

use Illuminate\Support\Facades\Cookie;

class CookieService
{
    /**
     * Set cookie.
     *
     * @param  int  $minutes  default 24 hours
     * @return \Symfony\Component\HttpFoundation\Cookie
     */
    public function set(string $name, string $value, int $minutes = 1440, ?string $path = null, ?string $domain = null, ?bool $secure = null, bool $httpOnly = true, bool $raw = false, ?string $sameSite = null)
    {
        return Cookie::make($name, $value, $minutes, $path, $domain, $secure, $httpOnly, $raw, $sameSite);
    }

    /**
     * Set cookie forever.
     *
     * @return \Symfony\Component\HttpFoundation\Cookie
     */
    public function setForever(string $name, string $value, ?string $path = null, ?string $domain = null, ?bool $secure = null, bool $httpOnly = true, bool $raw = false, ?string $sameSite = null)
    {
        return Cookie::forever($name, $value, $path, $domain, $secure, $httpOnly, $raw, $sameSite);

    }

    /**
     * Get cookie by key
     *
     * @param  mixed  $default
     */
    public function get(?string $key, $default = null): array|string|null
    {
        return Cookie::get($key, $default);
    }

    /**
     * check if cookie exists.
     */
    public function has(string $name): bool
    {
        return Cookie::has($name);
    }

    /**
     * Expire the given cookie.
     *
     * @return \Symfony\Component\HttpFoundation\Cookie
     */
    public function delete(string $name, ?string $path = null, ?string $domain = null)
    {
        return Cookie::forget($name, $path, $domain);
    }
}
