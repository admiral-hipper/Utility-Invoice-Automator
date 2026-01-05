<?php

namespace App\Filament\Resources\InvoiceResource\Widgets;

use App\Enums\InvoiceStatus;
use App\Models\Invoice;
use Filament\Widgets\ChartWidget;

class InvoicePaidChart extends ChartWidget
{
    protected static ?string $heading = 'Invoices: Paid vs Sent (last 6 months)';

    protected int|string|array $columnSpan = 'full';

    public static function canView(): bool
    {
        $user = auth()->user();

        return $user
            && ($user->role === \App\Enums\UserRole::ADMIN); // подстрой под свою модель/enum
    }

    protected function getData(): array
    {
        $from = now()->subMonths(5)->startOfMonth();

        $rows = Invoice::query()
            ->selectRaw('period,
                SUM(CASE WHEN status = ? THEN 1 ELSE 0 END) as paid_cnt,
                SUM(CASE WHEN sent_at IS NOT NULL THEN 1 ELSE 0 END) as sent_cnt
            ', [InvoiceStatus::PAID->value])
            ->whereNotNull('period')
            ->where('period', '>=', $from->format('Y-m'))
            ->groupBy('period')
            ->orderBy('period')->get();

        return [
            'datasets' => [
                [
                    'label' => 'Paid',
                    'data' => $rows->pluck('paid_cnt')->all(),
                    'borderColor' => '#1eb61cff',
                ],
                [
                    'label' => 'Sent',
                    'data' => $rows->pluck('sent_cnt')->all(),
                    'borderColor' => '#9BD0F5',
                ],
            ],
            'labels' => $rows->pluck('period')->all(),
        ];
    }

    protected function getType(): string
    {
        return 'bar';
    }
}
