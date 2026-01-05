<?php

namespace App\Filament\Resources\InvoiceResource\Pages;

use App\Filament\Resources\InvoiceResource;
use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewInvoice extends ViewRecord
{
    protected static string $resource = InvoiceResource::class;

    public function getActions(): array
    {
        return [
            EditAction::make('Edit'),
            Action::make('show')
                ->label('Show')
                ->icon('heroicon-o-eye')
                ->color('blue')
                ->url(fn ($record) => route('invoice.pdf.show', $record))
                ->openUrlInNewTab(),
            Action::make('pdf')
                ->label('PDF')
                ->icon('heroicon-m-document-arrow-down')
                ->url(fn ($record) => route('invoice.pdf.download', $record))
                ->openUrlInNewTab()->color('danger'),

        ];
    }
}
