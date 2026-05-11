<?php

namespace App\Helpers;

use App\Models\PaymentRecord;

class Pay extends Qs
{
    /**
     * Get all distinct years for which a student has payment records.
     *
     * @param int|string $st_id
     * @return \Illuminate\Support\Collection
     */
    public static function getDistinctPaymentYears(int|string $st_id)
    {
        return PaymentRecord::where('student_id', $st_id)->pluck('year')->unique();
    }

    /**
     * Generate a unique payment reference code (e.g., "2025/123456").
     *
     * @return string
     */
    public static function generateReferenceCode(): string
    {
        return date('Y') . '/' . mt_rand(10000, 999999);
    }

    /**
     * Return an associative array of payment status keys and display labels.
     *
     * @return array
     */
    public static function getPaymentStatusOptions(): array
    {
        return [
            'paid' => 'Paid',
            'unpaid' => 'Unpaid',
            'in_progress' => 'In Progress',
        ];
    }

    /**
     * Get the default currency unit (symbol) used across the application.
     *
     * @return string
     */
    public static function getDefaultCurrencySymbol(): string
    {
        return 'TSh';
    }
}
