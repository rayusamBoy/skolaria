<?php

namespace App\Repositories;

use App\Models\Setting;

class SettingRepo
{
    /*********** Settings ***************/

    /**
     * Get a setting by its key.
     */
    public function get(string $key): ?string
    {
        return Setting::where('key', $key)->value('value');
    }

    /**
     * Create a new setting key/value pair.
     */
    public function create(string $key, string $value): Setting
    {
        return Setting::create(['key' => $key, 'value' => $value]);
    }

    /**
     * Update the value of an existing setting by its key.
     *
     * @return int Number of updated rows
     */
    public function update(string $key, ?string $value): int
    {
        return Setting::where('key', $key)->update(['value' => $value]);
    }

    /**
     * Update or create the setting key/value pair.
     */
    public function updateOrCreate(string $key, ?string $value): Setting
    {
        return Setting::updateOrCreate(['key' => $key], ['value' => $value]);
    }

    /**
     * Change the key name of a setting.
     *
     * @return int Number of updated rows
     */
    public function updateKey(string $old_key, string $new_key): int
    {
        return Setting::where('key', $old_key)->update(['key' => $new_key]);
    }

    /**
     * Delete a setting by its key.
     *
     * @return int Number of deleted rows
     */
    public function delete(string $key): int
    {
        return Setting::where('key', $key)->delete();
    }
}
