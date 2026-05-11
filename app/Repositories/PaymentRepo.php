<?php

namespace App\Repositories;

use App\Helpers\Qs;
use App\Models\Payment;
use App\Models\PaymentRecord;
use App\Models\Receipt;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;

class PaymentRepo
{
    /*********** Payment ***************/

    /**
     * Get all soft‑deleted payments.
     *
     * @return Collection
     */
    public function getTrashedPayments(): Collection
    {
        return Payment::onlyTrashed()->get();
    }

    /**
     * Restore a soft‑deleted payment by ID.
     *
     * @param int $id
     * @return int
     */
    public function restorePayment(int $id): int
    {
        return Payment::where('id', $id)->restore();
    }

    /**
     * Permanently delete a soft‑deleted payment.
     *
     * @param int $id
     * @return int
     */
    public function forceDeletePayment(int $id): int
    {
        return Payment::where('id', $id)->forceDelete();
    }

    /**
     * Get a query builder for payments filtered by conditions, with class loaded.
     *
     * @param array $where
     * @return Builder
     */
    public function getPaymentsByConditions(array $where): Builder
    {
        return Payment::where($where)->with('my_class');
    }

    /**
     * Get a query builder for payments that are **not** class‑specific (i.e., global fees).
     *
     * @param array $where
     * @return Builder
     */
    public function getGlobalPayments(array $where): Builder
    {
        return Payment::whereNull('my_class_id')->where($where)->with('my_class');
    }

    /**
     * Get the query builder for currently active payments (current session).
     *
     * @return Builder
     */
    public function getActivePayments(): Builder
    {
        return $this->getPaymentsByConditions(['year' => Qs::getCurrentSession()]);
    }

    /**
     * Get all distinct payment years.
     *
     * @return Collection
     */
    public function getDistinctPaymentYears(): Collection
    {
        return Payment::select('year')->distinct()->get();
    }

    /**
     * Find a payment by its primary key.
     *
     * @param int $id
     * @return Payment|null
     */
    public function findPayment(int $id): ?Payment
    {
        return Payment::find($id);
    }

    /**
     * Create a new payment.
     *
     * @param array $data
     * @return Payment
     */
    public function createPayment(array $data): Payment
    {
        return Payment::create($data);
    }

    /**
     * Update a payment by ID.
     *
     * @param int   $id
     * @param array $data
     * @return int
     */
    public function updatePayment(int $id, array $data): int
    {
        return Payment::where('id', $id)->update($data);
    }

    /**
     * Delete a payment by ID (soft delete).
     *
     * @param int $id
     * @return int
     */
    public function deletePayment(int $id): int
    {
        return Payment::destroy($id);
    }

    /************** PAYMENT RECORDS ***************/

    /**
     * Get the query builder for all payment records of a student, optionally filtered by year.
     *
     * @param int        $st_id
     * @param string|null $year
     * @return Builder
     */
    public function getStudentPaymentRecords(int $st_id, ?string $year = null): Builder
    {
        $data = ['student_id' => $st_id];
        if ($year) {
            $data['year'] = $year;
        }
        return $this->getPaymentRecords($data);
    }

    /**
     * Get a query builder for payment records, ordered by a column.
     *
     * @param array  $data   conditions
     * @param string $order  column to order by
     * @param string $dir    direction
     * @return Builder
     */
    public function getPaymentRecords(array $data, string $order = 'year', string $dir = 'desc'): Builder
    {
        return PaymentRecord::orderBy($order, $dir)->where($data)->with('payment');
    }

    /**
     * Create a new payment record if one doesn’t already exist.
     *
     * @param array $data
     * @return PaymentRecord
     */
    public function firstOrCreatePaymentRecord(array $data): PaymentRecord
    {
        return PaymentRecord::firstOrCreate($data);
    }

    /**
     * Find a payment record by ID, throw 404 if missing.
     *
     * @param int $id
     * @return PaymentRecord
     */
    public function findPaymentRecord(int $id): PaymentRecord
    {
        return PaymentRecord::findOrFail($id);
    }

    /**
     * Get a single column value from the **payments** table, not payment records.
     *
     * @param int    $id       payment ID
     * @param string $col_name column name
     * @return mixed
     */
    public static function getPaymentColumnValue(int $id, string $col_name): mixed
    {
        return Payment::where('id', $id)->value($col_name);
    }

    /**
     * Update a payment record by ID.
     *
     * @param int   $id
     * @param array $data
     * @return int
     */
    public function updatePaymentRecord(int $id, array $data): int
    {
        return PaymentRecord::where('id', $id)->update($data);
    }

    /**
     * Get all paid payment records whose parent payment still exists.
     *
     * @return Collection
     */
    public function getPaidRecordsWithValidPayment(): Collection
    {
        // Only loads records where the related payment is not null
        return PaymentRecord::where('is_paid', true)
            ->with('payment')
            ->get()
            ->whereNotNull('payment');
    }

    /************** PAYMENT RECEIPTS ***************/

    /**
     * Create a new receipt.
     *
     * @param array $data
     * @return Receipt
     */
    public function createReceipt(array $data): Receipt
    {
        return Receipt::create($data); // 'create' is case-insensitive in PHP
    }

    /**
     * Delete receipts matching conditions.
     *
     * @param array $data
     * @return int
     */
    public function deleteReceipts(array $data): int
    {
        return Receipt::where($data)->delete();
    }
}
