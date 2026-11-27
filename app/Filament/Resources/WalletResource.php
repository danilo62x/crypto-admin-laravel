<?php

namespace App\Filament\Resources;

use App\Filament\Resources\WalletResource\Pages;
use App\Filament\Resources\WalletResource\RelationManagers;
use App\Models\Wallet;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Infolists;
use Filament\Infolists\Infolist;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\Summarizers\Sum;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class WalletResource extends Resource
{
    protected static ?string $model = Wallet::class;
    protected static ?string $recordTitleAttribute = 'address';
    protected static ?string $navigationIcon = 'heroicon-o-wallet';
    protected static ?string $navigationGroup = 'Exchange';
    protected static ?string $navigationLabel = 'Carteiras';
    protected static ?string $modelLabel = 'Carteira';
    protected static ?string $pluralModelLabel = 'Carteiras';
    protected static ?int $navigationSort = 1;

    public static function getGloballySearchableAttributes(): array
    {
        return ['address', 'user.name', 'user.email'];
    }

    public static function getGlobalSearchResultDetails($record): array
    {
        return [
            'Usuário' => $record->user?->name ?? '—',
            'Moeda'   => $record->coin?->symbol ?? '—',
            'Saldo'   => number_format((float) $record->balance, 8) . ' ' . ($record->coin?->symbol ?? ''),
        ];
    }

    public static function getNavigationBadge(): ?string
    {
        return (string) static::getModel()::count();
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Dados da carteira')
                    ->description('Associe uma carteira a um usuário e uma moeda.')
                    ->icon('heroicon-o-wallet')
                    ->columns(2)
                    ->schema([
                        Forms\Components\Select::make('user_id')
                            ->label('Usuário')
                            ->relationship('user', 'name')
                            ->searchable()
                            ->preload()
                            ->required(),
                        Forms\Components\Select::make('coin_id')
                            ->label('Moeda')
                            ->relationship('coin', 'name')
                            ->searchable()
                            ->preload()
                            ->required(),
                        Forms\Components\TextInput::make('address')
                            ->label('Endereço')
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->maxLength(100)
                            ->placeholder('0x...')
                            ->columnSpanFull(),
                        Forms\Components\TextInput::make('balance')
                            ->label('Saldo')
                            ->numeric()
                            ->default(0)
                            ->minValue(0)
                            ->step(0.00000001)
                            ->helperText('Saldo atual em unidades da moeda.'),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('user.avatar')
                    ->label('')
                    ->circular()
                    ->size(36)
                    ->defaultImageUrl(fn (Wallet $record): string => 'https://ui-avatars.com/api/?background=465fff&color=fff&name=' . urlencode($record->user?->name ?? '?')),
                Tables\Columns\TextColumn::make('user.name')
                    ->label('Usuário')
                    ->description(fn (Wallet $record): string => $record->user?->email ?? '—')
                    ->searchable()
                    ->sortable()
                    ->weight('medium'),
                Tables\Columns\TextColumn::make('coin.symbol')
                    ->label('Moeda')
                    ->badge()
                    ->color('warning')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('balance')
                    ->label('Saldo')
                    ->sortable()
                    ->formatStateUsing(fn (Wallet $record): string => number_format((float) $record->balance, 8) . ' ' . ($record->coin?->symbol ?? ''))
                    ->summarize(Sum::make()->label('Total')
                        ->formatStateUsing(fn ($state): string => number_format((float) $state, 8))),
                Tables\Columns\TextColumn::make('address')
                    ->label('Endereço')
                    ->copyable()
                    ->limit(20)
                    ->toggleable(),
                Tables\Columns\TextColumn::make('transactions_count')
                    ->label('Transações')
                    ->counts('transactions')
                    ->badge()
                    ->color('gray')
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Criada')
                    ->dateTime('d/m/Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('coin')
                    ->label('Moeda')
                    ->relationship('coin', 'symbol')
                    ->searchable()
                    ->preload()
                    ->multiple(),
                Tables\Filters\SelectFilter::make('user')
                    ->label('Usuário')
                    ->relationship('user', 'name')
                    ->searchable()
                    ->preload(),
                Tables\Filters\Filter::make('has_balance')
                    ->label('Com saldo > 0')
                    ->query(fn (Builder $query): Builder => $query->where('balance', '>', 0))
                    ->toggle(),
                Tables\Filters\Filter::make('created_at')
                    ->form([
                        Forms\Components\DatePicker::make('from')->label('Criada de'),
                        Forms\Components\DatePicker::make('until')->label('Criada até'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when($data['from'], fn (Builder $q, $v): Builder => $q->whereDate('created_at', '>=', $v))
                            ->when($data['until'], fn (Builder $q, $v): Builder => $q->whereDate('created_at', '<=', $v));
                    }),
                Tables\Filters\TrashedFilter::make(),
            ])
            ->actions([
                Tables\Actions\ActionGroup::make([
                    Tables\Actions\ViewAction::make(),
                    Tables\Actions\EditAction::make(),
                    Tables\Actions\DeleteAction::make(),
                    Tables\Actions\RestoreAction::make(),
                    Tables\Actions\ForceDeleteAction::make(),
                ]),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    Tables\Actions\RestoreBulkAction::make(),
                    Tables\Actions\ForceDeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('created_at', 'desc')
            ->striped();
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Infolists\Components\Split::make([
                    Infolists\Components\Section::make('Carteira')
                        ->schema([
                            Infolists\Components\TextEntry::make('user.name')
                                ->label('Usuário')
                                ->weight('bold'),
                            Infolists\Components\TextEntry::make('user.email')
                                ->label('Email')
                                ->icon('heroicon-o-envelope')
                                ->copyable(),
                            Infolists\Components\TextEntry::make('coin.name')
                                ->label('Moeda'),
                            Infolists\Components\TextEntry::make('coin.symbol')
                                ->label('Símbolo')
                                ->badge()
                                ->color('warning'),
                            Infolists\Components\TextEntry::make('address')
                                ->label('Endereço')
                                ->copyable()
                                ->columnSpanFull(),
                        ])
                        ->columns(2),
                    Infolists\Components\Section::make('Saldo')
                        ->schema([
                            Infolists\Components\TextEntry::make('balance')
                                ->label('Saldo atual')
                                ->formatStateUsing(fn (Wallet $record): string => number_format((float) $record->balance, 8) . ' ' . ($record->coin?->symbol ?? ''))
                                ->size('lg')
                                ->weight('bold')
                                ->color('success'),
                            Infolists\Components\TextEntry::make('transactions_count')
                                ->label('Total de transações')
                                ->state(fn (Wallet $record): int => $record->transactions()->count())
                                ->badge()
                                ->color('gray'),
                            Infolists\Components\TextEntry::make('created_at')
                                ->label('Criada em')
                                ->dateTime('d/m/Y H:i'),
                        ])
                        ->grow(false),
                ])->from('md'),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            RelationManagers\TransactionsRelationManager::class,
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->withoutGlobalScopes([SoftDeletingScope::class]);
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListWallets::route('/'),
            'create' => Pages\CreateWallet::route('/create'),
            'view'   => Pages\ViewWallet::route('/{record}'),
            'edit'   => Pages\EditWallet::route('/{record}/edit'),
        ];
    }
}
