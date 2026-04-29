<?php

namespace App\Repositories;


use App\Models\Setting;

class SettingRepo
{
    /*********** Setting ***************/
    public function create(string $type, string $desc)
    {
        return Setting::create(['type' => $type, 'description' => $desc]);
    }

    public function update($type, $desc)
    {
        return Setting::where('type', $type)->update(['description' => $desc]);
    }

    public function updateType(string $old_type, string $new_type)
    {
        return Setting::where('type', $old_type)->update(['type' => $new_type]);
    }

    public function get(string $type)
    {
        return Setting::where('type', $type)->get();
    }

    public function all()
    {
        return Setting::all();
    }

    public function pluck($column, $key = null)
    {
        return Setting::pluck($column, $key);
    }
}
