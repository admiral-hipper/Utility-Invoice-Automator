<?php

namespace App\Filament\Resources\InvoiceResource\Widgets;

use App\Enums\InvoiceStatus;
use App\Models\Invoice;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\Auth;

class InvoiceStatsOverview extends BaseWidget
{
    protected function getStats(): array
    {

        if (! Auth::user()->isAdmin()) {
            $customerIds = Auth::user()->customers->pluck('id');
            $stats = Invoice::query()
                ->whereIn('customer_id', $customerIds)
                ->selectRaw(
                    '
        SUM(CASE WHEN sent_at IS NOT NULL THEN 1 ELSE 0 END) AS sent,
        SUM(CASE WHEN status = ? THEN 1 ELSE 0 END) AS paid,
        SUM(CASE WHEN status = ? THEN 1 ELSE 0 END) AS issued,
        SUM(CASE WHEN status = ? AND due_date IS NOT NULL AND DATE(due_date) < ? THEN 1 ELSE 0 END) AS overdue
        ',
                    [
                        InvoiceStatus::PAID->value,
                        InvoiceStatus::ISSUED->value,
                        InvoiceStatus::ISSUED->value,
                        now()->toDateString(),
                    ]
                )
                ->first();

            $total = Invoice::query()->whereIn('customer_id', $customerIds)->count();
        } else {
            $stats = Invoice::query()
                ->selectRaw(
                    '
        SUM(CASE WHEN sent_at IS NOT NULL THEN 1 ELSE 0 END) AS sent,
        SUM(CASE WHEN status = ? THEN 1 ELSE 0 END) AS paid,
        SUM(CASE WHEN status = ? THEN 1 ELSE 0 END) AS issued,
        SUM(CASE WHEN status = ? AND due_date IS NOT NULL AND DATE(due_date) < ? THEN 1 ELSE 0 END) AS overdue
        ',
                    [
                        InvoiceStatus::PAID->value,
                        InvoiceStatus::ISSUED->value,
                        InvoiceStatus::ISSUED->value,
                        now()->toDateString(),
                    ]
                )
                ->first();

            $total = Invoice::query()->count();
        }

        return [
            Stat::make('Total invoices', $total),
            Stat::make('Sent', $stats->sent),
            Stat::make('Paid', $stats->paid),
            Stat::make('Overdue', $stats->overdue),
            Stat::make('Issued (unpaid)', $stats->issued),
        ];
    }
}
