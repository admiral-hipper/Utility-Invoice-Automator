<?php

namespace App\Filament\Resources\CustomerResource\RelationManagers;

use App\Enums\InvoiceStatus;
use App\Models\Invoice;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Infolist;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Actions\Action;
use Filament\Tables\Table;

class InvoicesRelationManager extends RelationManager
{
    protected static string $relationship = 'invoices';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('invoice_no')
                    ->required()
                    ->maxLength(255),
            ]);
    }

    public function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                TextEntry::make('invoice_no'),
                TextEntry::make('period')->color('warning'),
                TextEntry::make('issued_at')->default('-'),
                TextEntry::make('status')->badge()
                    ->color(fn (string $state): string => match ($state) {
                        InvoiceStatus::DRAFT->value => 'gray',
                        InvoiceStatus::PAID->value => 'success',
                        InvoiceStatus::CANCELED->value => 'danger',
                        InvoiceStatus::ISSUED->value => 'warning',
                    }),
                TextEntry::make('pdf_path')->label('PDF path')->default('Not generated'),
                TextEntry::make('due_date')->dateTime('Y-m-d'),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('invoice_no')
            ->columns([
                Tables\Columns\TextColumn::make('invoice_no'),
                Tables\Columns\TextColumn::make('status')->badge()
                    ->color(fn (string $state): string => match ($state) {
                        InvoiceStatus::DRAFT->value => 'gray',
                        InvoiceStatus::PAID->value => 'success',
                        InvoiceStatus::CANCELED->value => 'danger',
                        InvoiceStatus::ISSUED->value => 'warning',
                    }),
                Tables\Columns\TextColumn::make('created_at')->date('Y-m-d H:i:s')->sortable(),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make(),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
                Action::make('pdf')
                    ->label('PDF')
                    ->icon('heroicon-o-eye')
                    ->url(fn ($record) => route('invoice.pdf.show', $record))
                    ->openUrlInNewTab(),
                Action::make('delete')->color('danger')->icon('heroicon-o-trash')
                    ->action(fn (Invoice $record) => $record->delete())
                    ->requiresConfirmation(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }
}
