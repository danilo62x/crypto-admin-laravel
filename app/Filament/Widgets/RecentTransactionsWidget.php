<?php

namespace App\Filament\Widgets;

use App\Models\Transaction;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class RecentTransactionsWidget extends BaseWidget
{
    protected static ?int $sort = 3;

    protected int|string|array $columnSpan = 'full';

    protected static ?string $heading = 'Transações recentes';

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Transaction::query()
                    ->with(['wallet.user', 'wallet.coin'])
                    ->latest()
                    ->limit(8)
            )
            ->columns([
                Tables\Columns\TextColumn::make('wallet.user.name')
                    ->label('Usuário')
                    ->default('—'),
                Tables\Columns\TextColumn::make('wallet.coin.symbol')
                    ->label('Moeda')
                    ->badge()
                    ->color('warning'),
                Tables\Columns\TextColumn::make('type')
                    ->label('Tipo')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'deposito' => 'success',
                        'saque'    => 'danger',
                        'trade'    => 'info',
                        default    => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'deposito' => 'Depósito',
                        'saque'    => 'Saque',
                        'trade'    => 'Trade',
                        default    => $state,
                    }),
                Tables\Columns\TextColumn::make('amount')
                    ->label('Valor')
                    ->formatStateUsing(fn (Transaction $record): string => number_format((float) $record->amount, 8)),
                Tables\Columns\TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'concluida' => 'success',
                        'pendente'  => 'warning',
                        'falha'     => 'danger',
                        default     => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'concluida' => 'Concluída',
                        'pendente'  => 'Pendente',
                        'falha'     => 'Falha',
                        default     => $state,
                    }),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Data')
                    ->dateTime('d/m/Y H:i'),
            ])
            ->paginated(false);
    }
}
