<?php

namespace App\Repositories;

use App\Models\Pin;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

class PinRepo
{
    /*********** Pins ***************/

    /**
     * Insert one or more pins via the DB facade.
     *
     * @param array $values  single or multi‑dimensional array
     * @return bool
     */
    public function insertPins(array $values): bool
    {
        return DB::table('pins')->insert($values);
    }

    /**
     * Count currently valid (unused) pins.
     *
     * @return int
     */
    public function countValidPins(): int
    {
        return $this->getPinBuilder(['is_used' => false])->count();
    }

    /**
     * Update a pin by its primary key.
     *
     * @param int   $id
     * @param array $data
     * @return int  Number of updated rows
     */
    public function updatePin(int $id, array $data): int
    {
        return Pin::where('id', $id)->update($data);
    }

    /**
     * Get a query builder for a specific user/student pin.
     *
     * @param string $code
     * @param int    $user_id
     * @param int    $st_id
     * @return Builder
     */
    public function getUserPinBuilder(string $code, int $user_id, int $st_id): Builder
    {
        return $this->getPinBuilder(['code' => $code, 'user_id' => $user_id, 'student_id' => $st_id]);
    }

    /**
     * Get a query builder for a valid (unused) pin by its code.
     *
     * @param string $code
     * @return Builder
     */
    public function getValidPinByCode(string $code): Builder
    {
        return $this->getPinBuilder(['code' => $code, 'is_used' => false]);
    }

    /**
     * Delete all used pins.
     *
     * @return int  Number of deleted rows
     */
    public function deleteUsedPins(): int
    {
        return $this->getPinBuilder(['is_used' => true])->delete();
    }

    /**
     * Raw query builder for pins matching conditions.
     *
     * @param array $where
     * @return Builder
     */
    public function getPinBuilder(array $where): Builder
    {
        return Pin::where($where);
    }

    /**
     * Get all valid (unused) pins.
     *
     * @return Collection
     */
    public function getValidPins(): Collection
    {
        return $this->getPinBuilder(['is_used' => false])->get();
    }

    /**
     * Get all used pins with user and student relations.
     *
     * @return Collection
     */
    public function getUsedPins(): Collection
    {
        return $this->getPinBuilder(['is_used' => true])
            ->with(['user', 'student'])
            ->get();
    }
}
