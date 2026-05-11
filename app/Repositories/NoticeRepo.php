<?php

namespace App\Repositories;

use App\Models\Notice;
use Illuminate\Database\Eloquent\Collection;

class NoticeRepo
{
    /*********** Notices ***************/

    /**
     * Get all notices (or only those created by the current user if not a superadmin).
     *
     * @param array $with  Relations to eager load
     * @return Collection
     */
    public function getAllOrOwnNotices(array $with = ['user']): Collection
    {
        return Notice::with($with)
            ->when(
                !auth()->user()->hasRole(config('roles.default_roles.tenant.superadmin')),
                fn($query) => $query->where('creator_id', auth()->id())
            )
            ->get();
    }

    /**
     * Get all notices except those created by the current user (unless superadmin, who sees all).
     *
     * @return Collection
     */
    public function getAllExceptOwn(): Collection
    {
        return Notice::with('user')
            ->when(
                !auth()->user()->hasRole(config('roles.default_roles.tenant.superadmin')),
                fn($query) => $query->where('creator_id', '!=', auth()->id())
            )
            ->get();
    }

    /**
     * Get notices by ID (returns a collection, even for a single ID).
     *
     * @param mixed $id
     * @return Collection
     */
    public function getNoticesById($id): Collection
    {
        return Notice::where('id', $id)->get();
    }

    /**
     * Create a new notice.
     *
     * @param array $data
     * @return Notice
     */
    public function createNotice(array $data): Notice
    {
        return Notice::create($data);
    }

    /**
     * Update a notice by ID.
     *
     * @param int   $id
     * @param array $data
     * @return int
     */
    public function updateNotice(int $id, array $data): int
    {
        return Notice::where('id', $id)->update($data);
    }
}
