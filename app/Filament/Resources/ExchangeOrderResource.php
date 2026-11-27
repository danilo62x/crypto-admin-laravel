<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ExchangeOrderResource\Pages;
use App\Models\ExchangeOrder;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Infolists;
use Filament\Infolists\Infolist;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\Summarizers\Sum;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class ExchangeOrderResource extends Resource
{
    protected static ?string $model = ExchangeOrder::class;
    protected static ?string $recordTitleAttribute = 'pair';
    protected static ?string $navigationIcon = 'heroicon-o-chart-bar';
    protected static ?string $navigationGroup = 'Exchange';
    protected static ?string $navigationLabel = 'Ordens';
    protected static ?string $modelLabel = 'Ordem';
    protected static ?string $pluralModelLabel = 'Ordens';
    protected static ?int $navigationSort = 3;

    protected const PAIRS = [
        'BTC/BRL'  => 'BTC/BRL',
        'ETH/BRL'  => 'ETH/BRL',
        'SOL/BRL'  => 'SOL/BRL',
        'BNB/BRL'  => 'BNB/BRL',
        'ADA/BRL'  => 'ADA/BRL',
        'XRP/BRL'  => 'XRP/BRL',
        'DOGE/BRL' => 'DOGE/BRL',
        'AVAX/BRL' => 'AVAX/BRL',
    ];

    protected const SIDES = [
        'compra' => 'Compra',
        'venda'  => 'Venda',
    ];

    protected const STATUSES = [
        'pendente'  => 'Pendente',
        'executada' => 'Executada',
        'cancelada' => 'Cancelada',
    ];

    protected const STATUS_COLORS = [
        'pendente'  => 'warning',
        'executada' => 'success',
        'cancelada' => 'danger',
    ];

    public static function getGloballySearchableAttributes(): array
    {
        return ['pair', 'user.name', 'user.email'];
    }

    public static function getGlobalSearchResultDetails($record): array
    {
        return [
            'Par'     => $record->pair,
            'Lado'    => self::SIDES[$record->side] ?? $record->side,
            'Total'   => 'R$ ' . number_format((float) $record->total, 2, ',', '.'),
        ];
    }

    public static function getNavigationBadge(): ?string
    {
        return (string) static::getModel()::where('status', 'pendente')->count();
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'warning';
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Dados da ordem')
                    ->description('Par, lado e parâmetros de execução.')
                    ->icon('heroicon-o-chart-bar')
                    ->columns(2)
                    ->schema([
                        Forms\Components\Select::make('user_id')
                            ->label('Usuário')
                            ->relationship('user', 'name')
                            ->searchable()
                            ->preload()
                            ->required(),
                        Forms\Components\Select::make('status')
                            ->label('Status')
                            ->options(self::STATUSES)
                            ->default('pendente')
                            ->native(false)
                            ->required(),
                        Forms\Components\Select::make('pair')
                            ->label('Par')
                            ->options(self::PAIRS)
                            ->native(false)
                            ->searchable()
                            ->required(),
                        Forms\Components\Select::make('side')
                            ->label('Lado')
                            ->options(self::SIDES)
                            ->native(false)
                            ->required(),
                        Forms\Components\TextInput::make('price')
                            ->label('Preço unitário (BRL)')
                            ->numeric()
                            ->prefix('R$')
                            ->step(0.00000001)
                            ->required()
                            ->minValue(0)
                            ->live(onBlur: true),
                        Forms\Components\TextInput::make('amount')
                            ->label('Quantidade')
                            ->numeric()
                            ->step(0.00000001)
                            ->required()
                            ->minValue(0)
                            ->live(onBlur: true),
                        Forms\Components\Placeholder::make('total_preview')
                            ->label('Total estimado')
                            ->content(function (Get $get): string {
                                $price  = (float) ($get('price') ?? 0);
                                $amount = (float) ($get('amount') ?? 0);
                                $total  = $price * $amount;
                                return 'R$ ' . number_format($total, 2, ',', '.');
                            })
                            ->columnSpanFull(),
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
                    ->defaultImageUrl(fn (ExchangeOrder $record): string => 'https://ui-avatars.com/api/?background=465fff&color=fff&name=' . urlencode($record->user?->name ?? '?')),
                Tables\Columns\TextColumn::make('user.name')
                    ->label('Usuário')
                    ->description(fn (ExchangeOrder $record): string => $record->user?->email ?? '—')
                    ->searchable()
                    ->sortable()
                    ->weight('medium'),
                Tables\Columns\TextColumn::make('pair')
                    ->label('Par')
                    ->badge()
                    ->color('gray')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('side')
                    ->label('Lado')
                    ->badge()
                    ->color(fn (string $state): string => $state === 'compra' ? 'success' : 'danger')
                    ->formatStateUsing(fn (string $state): string => self::SIDES[$state] ?? $state),
                Tables\Columns\TextColumn::make('price')
                    ->label('Preço (BRL)')
                    ->money('BRL')
                    ->sortable(),
                Tables\Columns\TextColumn::make('amount')
                    ->label('Qtd')
                    ->formatStateUsing(fn (ExchangeOrder $record): string => number_format((float) $record->amount, 8))
                    ->sortable(),
                Tables\Columns\TextColumn::make('total')
                    ->label('Total (BRL)')
                    ->money('BRL')
                    ->sortable()
                    ->summarize(Sum::make()->label('Total geral')->money('BRL')),
                Tables\Columns\TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn (string $state): string => self::STATUS_COLORS[$state] ?? 'gray')
                    ->formatStateUsing(fn (string $state): string => self::STATUSES[$state] ?? $state),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Data')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('pair')
                    ->label('Par')
                    ->options(self::PAIRS)
                    ->multiple(),
                Tables\Filters\SelectFilter::make('side')
                    ->label('Lado')
                    ->options(self::SIDES),
                Tables\Filters\SelectFilter::make('status')
                    ->label('Status')
                    ->options(self::STATUSES)
                    ->multiple(),
                Tables\Filters\Filter::make('created_at')
                    ->form([
                        Forms\Components\DatePicker::make('from')->label('De'),
                        Forms\Components\DatePicker::make('until')->label('Até'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when($data['from'], fn (Builder $q, $v): Builder => $q->whereDate('created_at', '>=', $v))
                            ->when($data['until'], fn (Builder $q, $v): Builder => $q->whereDate('created_at', '<=', $v));
                    }),
                Tables\Filters\TrashedFilter::make(),
            ])
            ->actions([
                Tables\Actions\Action::make('execute')
                    ->label('Executar')
                    ->icon('heroicon-o-bolt')
                    ->color('success')
                    ->requiresConfirmation()
                    ->visible(fn (ExchangeOrder $record): bool => $record->status === 'pendente')
                    ->action(function (ExchangeOrder $record): void {
                        $record->update(['status' => 'executada']);
                        Notification::make()->title('Ordem executada')->success()->send();
                    }),
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
                    Tables\Actions\BulkAction::make('cancel')
                        ->label('Cancelar selecionadas')
                        ->icon('heroicon-o-x-circle')
                        ->color('danger')
                        ->requiresConfirmation()
                        ->action(fn ($records) => $records->each->update(['status' => 'cancelada']))
                        ->deselectRecordsAfterCompletion(),
                    Tables\Actions\DeleteBulkAction::make(),
                    Tables\Actions\RestoreBulkAction::make(),
                    Tables\Actions\ForceDeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Infolists\Components\Split::make([
                    Infolists\Components\Section::make('Ordem')
                        ->schema([
                            Infolists\Components\TextEntry::make('user.name')
                                ->label('Usuário')
                                ->weight('bold'),
                            Infolists\Components\TextEntry::make('user.email')
                                ->label('Email')
                                ->icon('heroicon-o-envelope')
                                ->copyable(),
                            Infolists\Components\TextEntry::make('pair')
                                ->label('Par')
                                ->badge()
                                ->color('gray'),
                            Infolists\Components\TextEntry::make('side')
                                ->label('Lado')
                                ->badge()
                                ->color(fn (string $state): string => $state === 'compra' ? 'success' : 'danger')
                                ->formatStateUsing(fn (string $state): string => self::SIDES[$state] ?? $state),
                            Infolists\Components\TextEntry::make('status')
                                ->label('Status')
                                ->badge()
                                ->color(fn (string $state): string => self::STATUS_COLORS[$state] ?? 'gray')
                                ->formatStateUsing(fn (string $state): string => self::STATUSES[$state] ?? $state),
                            Infolists\Components\TextEntry::make('created_at')
                                ->label('Criada em')
                                ->dateTime('d/m/Y H:i'),
                        ])
                        ->columns(2),
                    Infolists\Components\Section::make('Valores')
                        ->schema([
                            Infolists\Components\TextEntry::make('price')
                                ->label('Preço unitário')
                                ->money('BRL'),
                            Infolists\Components\TextEntry::make('amount')
                                ->label('Quantidade')
                                ->formatStateUsing(fn (ExchangeOrder $record): string => number_format((float) $record->amount, 8)),
                            Infolists\Components\TextEntry::make('total')
                                ->label('Total')
                                ->money('BRL')
                                ->size('lg')
                                ->weight('bold')
                                ->color('success'),
                        ])
                        ->grow(false),
                ])->from('md'),
            ]);
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->withoutGlobalScopes([SoftDeletingScope::class]);
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListExchangeOrders::route('/'),
            'create' => Pages\CreateExchangeOrder::route('/create'),
            'view'   => Pages\ViewExchangeOrder::route('/{record}'),
            'edit'   => Pages\EditExchangeOrder::route('/{record}/edit'),
        ];
    }
}
