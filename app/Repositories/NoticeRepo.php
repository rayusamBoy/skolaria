<?php

namespace App\Repositories;

use App\Models\Notice;

class NoticeRepo
{
    /*********** Notices ***************/

    public function all()
    {
        return Notice::all();
    }

    public function allOrOnlyMine(array $with = ['user'])
    {
        return auth()->user()->hasRole(config('roles.default_roles.tenant.superadmin')) ? Notice::with($with)->get() : Notice::with($with)->get()->where("from_id", auth()->id());
    }

    public function allExceptAuth()
    {
        return auth()->user()->hasRole(config('roles.default_roles.tenant.superadmin')) ? Notice::with('user')->get() : Notice::with('user')->get()->where("from_id", "!=", auth()->id());
    }

    public function getIds()
    {
        return Notice::pluck('id');
    }

    public function getById($id)
    {
        return Notice::where('id', $id)->get();
    }

    public function create($data)
    {
        return Notice::create($data);
    }

    public function update($id, $data)
    {
        return Notice::find($id)->update($data);
    }

    public function delete($id)
    {
        return Notice::destroy($id);
    }
}
