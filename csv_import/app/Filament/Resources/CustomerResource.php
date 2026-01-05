<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CustomerResource\Pages;
use App\Filament\Resources\CustomerResource\RelationManagers\InvoicesRelationManager;
use App\Filament\Resources\CustomerResource\RelationManagers\UsersRelationManager;
use App\Models\Customer;
use App\Models\User;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class CustomerResource extends Resource
{
    protected static ?string $model = Customer::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Select::make('user_id')
                    ->label('User')
                    ->relationship(
                        name: 'user',
                        titleAttribute: 'email',
                        modifyQueryUsing: function (Builder $query) {
                            $user = auth()->user();

                            if ($user && method_exists($user, 'isAdmin') && $user->isAdmin()) {
                                return;
                            }
                            $query->whereKey(auth()->id());
                        },
                    )
                    ->getOptionLabelFromRecordUsing(fn (User $record) => "{$record->id}. {$record->name} ({$record->email})")
                    ->searchable(['email', 'id'])
                    ->preload()
                    ->required()
                    ->default(fn () => auth()->id())
                    ->disabled(fn () => ! auth()->user()?->isAdmin())
                    ->dehydrateStateUsing(fn ($state) => auth()->user()?->isAdmin() ? $state : auth()->id()),
                TextInput::make('full_name')->required(),
                TextInput::make('house_address')->required(),
                TextInput::make('apartment')->required(),
                TextInput::make('email')->email()->required(),
                TextInput::make('phone')->type('phone')->required(),

            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('full_name')->searchable(),
                TextColumn::make('email'),
                TextColumn::make('phone'),
                TextColumn::make('house_address')->searchable(),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make()->label(''),
                \Filament\Tables\Actions\ViewAction::make()->label(''),
                Tables\Actions\DeleteAction::make()->label('')->requiresConfirmation(),
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
            InvoicesRelationManager::class,
            UsersRelationManager::class,
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery();
        $user = Auth::user();

        if ($user && (method_exists($user, 'isAdmin') ? $user->isAdmin() : (bool) ($user->is_admin ?? false))) {
            return $query;
        }

        return $query->where('user_id', auth()->id());
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListCustomers::route('/'),
            'create' => Pages\CreateCustomer::route('/create'),
            'edit' => Pages\EditCustomer::route('/{record}/edit'),
            'view' => Pages\ViewCustomer::route('/{record}'),
        ];
    }
}
