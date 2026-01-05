<?php

namespace App\Filament\Resources;

use App\Enums\ImportStatus;
use App\Filament\Resources\ImportResource\Pages;
use App\Models\Import;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Infolists;
use Filament\Infolists\Infolist;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class ImportResource extends Resource
{
    protected static ?string $model = Import::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('file_path')->readOnly(),
                TextInput::make('total_rows')->readOnly(),
                Select::make('status')->options(ImportStatus::class),
                TextInput::make('errors')->readOnly(),
            ]);
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Infolists\Components\TextEntry::make('file_path'),
                Infolists\Components\TextEntry::make('period')->color('warning'),
                Infolists\Components\TextEntry::make('total_rows'),
                Infolists\Components\TextEntry::make('status')->badge()
                    ->color(fn (string $state): string => match ($state) {
                        ImportStatus::QUEUED->value => 'gray',
                        ImportStatus::PROCESSED->value => 'success',
                        ImportStatus::FAILED->value => 'danger',
                        ImportStatus::ARCHIDED->value => 'gray',
                    }),
                Infolists\Components\TextEntry::make('errors')->default('-'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('file_path'),
                TextColumn::make('period'),
                TextColumn::make('status')->sortable()
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        ImportStatus::QUEUED->value => 'gray',
                        ImportStatus::PROCESSED->value => 'success',
                        ImportStatus::FAILED->value => 'danger',
                        ImportStatus::ARCHIDED->value => 'gray',
                        default => 'draft'
                    }),
                TextColumn::make('created_at')->date('Y-m-d H:i:s')->sortable(),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\ViewAction::make(),
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
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListImports::route('/'),
            'create' => Pages\CreateImport::route('/create'),
            'edit' => Pages\EditImport::route('/{record}/edit'),
            'view' => Pages\ViewImports::route('/{record}'),
        ];
    }
}
