<?php

namespace App\Services\Invoice;

use Illuminate\Support\Facades\DB;

class InvoiceNumberService
{
    /**
     * period: 'YYYY-MM'
     * invoice_no: INV-YYYYMM-000001
     */
    public function nextInvoiceNo(string $period): array
    {
        $periodKey = preg_replace('/[^0-9\-]/', '', $period); // safety
        $yyyymm = str_replace('-', '', $periodKey);           // YYYYMM

        return DB::transaction(function () use ($periodKey, $yyyymm) {
            $row = DB::table('invoice_counters')
                ->where('period', $periodKey)
                ->lockForUpdate()
                ->first();

            if (!$row) {
                DB::table('invoice_counters')->insert([
                    'period' => $periodKey,
                    'next_number' => 2,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
                $n = 1;
            } else {
                $n = (int)$row->next_number;
                DB::table('invoice_counters')
                    ->where('period', $periodKey)
                    ->update([
                        'next_number' => $n + 1,
                        'updated_at' => now(),
                    ]);
            }

            $invoiceNo = sprintf('INV-%s-%06d', $yyyymm, $n);

            // Payment reference (для выписки/сверки). Можно менять формат без миграций.
            $prefix = config('billing.payment_ref_prefix', 'INV');
            $paymentRef = $prefix . ':' . $invoiceNo;

            return [$invoiceNo, $paymentRef];
        });
    }
}
