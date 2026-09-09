<?php

namespace App\Helpers;

use App\Models\PaymentRecord;
use Illuminate\Support\Collection;

class Pay extends Qs
{
    /**
     * Get all distinct years for which a student has payment records.
     *
     * @return Collection
     */
    public static function getDistinctPaymentYears(int|string $st_id)
    {
        return PaymentRecord::where('student_id', $st_id)->pluck('year')->unique();
    }

    /**
     * Generate a unique payment reference code (e.g., "2025/123456").
     */
    public static function generateReferenceCode(): string
    {
        return date('Y').'/'.mt_rand(10000, 999999);
    }

    /**
     * Return an associative array of payment status keys and display labels.
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
     */
    public static function getDefaultCurrencySymbol(): string
    {
        return 'TSh';
    }
}
