<?php

namespace App\Repositories;

use App\Models\Setting;

class SettingRepo
{
    /*********** Settings ***************/

    /**
     * Create a new setting key/value pair.
     *
     * @param string $key
     * @param string $value
     * @return Setting
     */
    public function create(string $key, string $value): Setting
    {
        return Setting::create(['key' => $key, 'value' => $value]);
    }

    /**
     * Update the value of an existing setting by its key.
     *
     * @param string $key
     * @param string|null $value
     * @return int  Number of updated rows
     */
    public function update(string $key, string|null $value): int
    {
        return Setting::where('key', $key)->update(['value' => $value]);
    }

    /**
     * Change the key name of a setting.
     *
     * @param string $old_key
     * @param string $new_key
     * @return int  Number of updated rows
     */
    public function updateKey(string $old_key, string $new_key): int
    {
        return Setting::where('key', $old_key)->update(['key' => $new_key]);
    }
}
