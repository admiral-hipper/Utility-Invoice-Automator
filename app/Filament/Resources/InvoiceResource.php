<?php

namespace App\Filament\Resources;

use App\Enums\InvoiceStatus;
use App\Filament\Resources\InvoiceResource\Pages;
use App\Filament\Resources\InvoiceResource\RelationManagers\ImportRelationManager;
use App\Models\Invoice;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Infolist;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Actions\Action;
use Filament\Tables\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class InvoiceResource extends Resource
{
    protected static ?string $model = Invoice::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('invoice_no'),
                DatePicker::make('period')->format('Y-m')->native(false)->displayFormat('Y-m'),
                Select::make('status')->options(InvoiceStatus::class),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('invoice_no')->searchable()->sortable(),
                TextColumn::make('period')->sortable(),
                TextColumn::make('status')->sortable()
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        InvoiceStatus::DRAFT->value => 'gray',
                        InvoiceStatus::PAID->value => 'success',
                        InvoiceStatus::CANCELED->value => 'danger',
                        InvoiceStatus::ISSUED->value => 'warning',
                    }),
                TextColumn::make('created_at')->date('Y-m-d H:i:s')->sortable(),
            ])
            ->filters([
                //
            ])
            ->actions([
                ViewAction::make('view'),
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
                    ->openUrlInNewTab(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            ImportRelationManager::class,
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery();

        $user = auth()->user();

        if ($user && ! $user->isAdmin()) {
            $query->whereHas('customer', fn (Builder $q) => $q->where('user_id', $user->id));
        }

        return $query;
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListInvoices::route('/'),
            'create' => Pages\CreateInvoice::route('/create'),
            'edit' => Pages\EditInvoice::route('/{record}/edit'),
            'view' => Pages\ViewInvoice::route('/{record}'),
        ];
    }

    public static function infolist(Infolist $infolist): Infolist
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
                        default => 'draft'
                    }),
                TextEntry::make('pdf_path')->label('PDF path')->default('Not generated'),
                TextEntry::make('due_date')->dateTime('Y-m-d'),
                TextEntry::make('sent_at')->dateTime('Y-m-d'),
            ]);
    }
}
