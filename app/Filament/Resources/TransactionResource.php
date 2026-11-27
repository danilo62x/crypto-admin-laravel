<?php

namespace App\Filament\Resources;

use App\Filament\Resources\TransactionResource\Pages;
use App\Models\Transaction;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Infolists;
use Filament\Infolists\Infolist;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\Summarizers\Sum;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class TransactionResource extends Resource
{
    protected static ?string $model = Transaction::class;
    protected static ?string $recordTitleAttribute = 'tx_hash';
    protected static ?string $navigationIcon = 'heroicon-o-arrows-right-left';
    protected static ?string $navigationGroup = 'Exchange';
    protected static ?string $navigationLabel = 'Transações';
    protected static ?string $modelLabel = 'Transação';
    protected static ?string $pluralModelLabel = 'Transações';
    protected static ?int $navigationSort = 2;

    protected const TYPES = [
        'deposito' => 'Depósito',
        'saque'    => 'Saque',
        'trade'    => 'Trade',
    ];

    protected const TYPE_COLORS = [
        'deposito' => 'success',
        'saque'    => 'danger',
        'trade'    => 'info',
    ];

    protected const STATUSES = [
        'pendente'  => 'Pendente',
        'concluida' => 'Concluída',
        'falha'     => 'Falha',
    ];

    protected const STATUS_COLORS = [
        'pendente'  => 'warning',
        'concluida' => 'success',
        'falha'     => 'danger',
    ];

    public static function getGloballySearchableAttributes(): array
    {
        return ['tx_hash', 'wallet.address', 'wallet.user.name'];
    }

    public static function getGlobalSearchResultDetails($record): array
    {
        return [
            'Tipo'    => self::TYPES[$record->type] ?? $record->type,
            'Status'  => self::STATUSES[$record->status] ?? $record->status,
            'Valor'   => number_format((float) $record->amount, 8),
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
                Forms\Components\Section::make('Dados da transação')
                    ->description('Detalhes do movimento financeiro na carteira.')
                    ->icon('heroicon-o-arrows-right-left')
                    ->columns(2)
                    ->schema([
                        Forms\Components\Select::make('wallet_id')
                            ->label('Carteira')
                            ->relationship('wallet', 'address')
                            ->searchable()
                            ->preload()
                            ->required()
                            ->columnSpanFull(),
                        Forms\Components\Select::make('type')
                            ->label('Tipo')
                            ->options(self::TYPES)
                            ->native(false)
                            ->required(),
                        Forms\Components\Select::make('status')
                            ->label('Status')
                            ->options(self::STATUSES)
                            ->default('pendente')
                            ->native(false)
                            ->required(),
                        Forms\Components\TextInput::make('amount')
                            ->label('Valor')
                            ->numeric()
                            ->step(0.00000001)
                            ->required()
                            ->minValue(0),
                        Forms\Components\TextInput::make('fee')
                            ->label('Taxa')
                            ->numeric()
                            ->step(0.00000001)
                            ->default(0)
                            ->minValue(0),
                        Forms\Components\TextInput::make('tx_hash')
                            ->label('Hash da transação')
                            ->unique(ignoreRecord: true)
                            ->maxLength(130)
                            ->placeholder('0x...')
                            ->columnSpanFull(),
                    ]),
                Forms\Components\Section::make('Confirmação & notas')
                    ->icon('heroicon-o-document-text')
                    ->collapsed()
                    ->columns(2)
                    ->schema([
                        Forms\Components\DateTimePicker::make('confirmed_at')
                            ->label('Confirmada em')
                            ->displayFormat('d/m/Y H:i'),
                        Forms\Components\Textarea::make('notes')
                            ->label('Observações')
                            ->rows(3)
                            ->columnSpanFull(),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('wallet.user.name')
                    ->label('Usuário')
                    ->description(fn (Transaction $record): string => $record->wallet?->coin?->symbol ?? '—')
                    ->searchable()
                    ->sortable()
                    ->weight('medium'),
                Tables\Columns\TextColumn::make('type')
                    ->label('Tipo')
                    ->badge()
                    ->color(fn (string $state): string => self::TYPE_COLORS[$state] ?? 'gray')
                    ->formatStateUsing(fn (string $state): string => self::TYPES[$state] ?? $state),
                Tables\Columns\TextColumn::make('amount')
                    ->label('Valor')
                    ->sortable()
                    ->formatStateUsing(fn (Transaction $record): string => number_format((float) $record->amount, 8))
                    ->summarize(Sum::make()->label('Total')
                        ->formatStateUsing(fn ($state): string => number_format((float) $state, 8))),
                Tables\Columns\TextColumn::make('fee')
                    ->label('Taxa')
                    ->formatStateUsing(fn (Transaction $record): string => number_format((float) $record->fee, 8))
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn (string $state): string => self::STATUS_COLORS[$state] ?? 'gray')
                    ->formatStateUsing(fn (string $state): string => self::STATUSES[$state] ?? $state),
                Tables\Columns\TextColumn::make('tx_hash')
                    ->label('Hash')
                    ->copyable()
                    ->limit(14)
                    ->toggleable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Data')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('type')
                    ->label('Tipo')
                    ->options(self::TYPES)
                    ->multiple(),
                Tables\Filters\SelectFilter::make('status')
                    ->label('Status')
                    ->options(self::STATUSES)
                    ->multiple(),
                Tables\Filters\SelectFilter::make('coin')
                    ->label('Moeda')
                    ->relationship('wallet.coin', 'symbol')
                    ->searchable()
                    ->preload(),
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
                Tables\Actions\Action::make('approve')
                    ->label('Aprovar saque')
                    ->icon('heroicon-o-check-badge')
                    ->color('success')
                    ->requiresConfirmation()
                    ->visible(fn (Transaction $record): bool => $record->type === 'saque' && $record->status === 'pendente')
                    ->action(function (Transaction $record): void {
                        $record->update([
                            'status'       => 'concluida',
                            'confirmed_at' => now(),
                        ]);
                        Notification::make()->title('Saque aprovado com sucesso')->success()->send();
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
                    Tables\Actions\BulkAction::make('approve_withdrawals')
                        ->label('Aprovar saques pendentes')
                        ->icon('heroicon-o-check-badge')
                        ->color('success')
                        ->requiresConfirmation()
                        ->action(function ($records): void {
                            $records
                                ->filter(fn (Transaction $r): bool => $r->type === 'saque' && $r->status === 'pendente')
                                ->each->update(['status' => 'concluida', 'confirmed_at' => now()]);
                        })
                        ->deselectRecordsAfterCompletion()
                        ->after(fn () => Notification::make()->title('Saques aprovados')->success()->send()),
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
                    Infolists\Components\Section::make('Transação')
                        ->schema([
                            Infolists\Components\TextEntry::make('wallet.user.name')
                                ->label('Usuário')
                                ->weight('bold'),
                            Infolists\Components\TextEntry::make('wallet.coin.symbol')
                                ->label('Moeda')
                                ->badge()
                                ->color('warning'),
                            Infolists\Components\TextEntry::make('type')
                                ->label('Tipo')
                                ->badge()
                                ->color(fn (string $state): string => self::TYPE_COLORS[$state] ?? 'gray')
                                ->formatStateUsing(fn (string $state): string => self::TYPES[$state] ?? $state),
                            Infolists\Components\TextEntry::make('status')
                                ->label('Status')
                                ->badge()
                                ->color(fn (string $state): string => self::STATUS_COLORS[$state] ?? 'gray')
                                ->formatStateUsing(fn (string $state): string => self::STATUSES[$state] ?? $state),
                            Infolists\Components\TextEntry::make('tx_hash')
                                ->label('Hash')
                                ->copyable()
                                ->placeholder('—')
                                ->columnSpanFull(),
                        ])
                        ->columns(2),
                    Infolists\Components\Section::make('Valores')
                        ->schema([
                            Infolists\Components\TextEntry::make('amount')
                                ->label('Valor')
                                ->formatStateUsing(fn (Transaction $record): string => number_format((float) $record->amount, 8) . ' ' . ($record->wallet?->coin?->symbol ?? ''))
                                ->size('lg')
                                ->weight('bold')
                                ->color('success'),
                            Infolists\Components\TextEntry::make('fee')
                                ->label('Taxa')
                                ->formatStateUsing(fn (Transaction $record): string => number_format((float) $record->fee, 8)),
                            Infolists\Components\TextEntry::make('confirmed_at')
                                ->label('Confirmada em')
                                ->dateTime('d/m/Y H:i')
                                ->placeholder('—'),
                            Infolists\Components\TextEntry::make('created_at')
                                ->label('Criada em')
                                ->dateTime('d/m/Y H:i'),
                        ])
                        ->grow(false),
                ])->from('md'),
                Infolists\Components\Section::make('Observações')
                    ->collapsed()
                    ->schema([
                        Infolists\Components\TextEntry::make('notes')->label('')->placeholder('Sem observações.'),
                    ]),
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
            'index'  => Pages\ListTransactions::route('/'),
            'create' => Pages\CreateTransaction::route('/create'),
            'view'   => Pages\ViewTransaction::route('/{record}'),
            'edit'   => Pages\EditTransaction::route('/{record}/edit'),
        ];
    }
}
