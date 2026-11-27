<?php

namespace App\Filament\Resources\WalletResource\RelationManagers;

use App\Models\Transaction;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Columns\Summarizers\Sum;
use Filament\Tables\Table;

class TransactionsRelationManager extends RelationManager
{
    protected static string $relationship = 'transactions';
    protected static ?string $title = 'Transações';
    protected static ?string $recordTitleAttribute = 'tx_hash';
    protected static ?string $icon = 'heroicon-o-arrows-right-left';

    protected const TYPES = [
        'deposito' => 'Depósito',
        'saque'    => 'Saque',
        'trade'    => 'Trade',
    ];

    protected const STATUSES = [
        'pendente'  => 'Pendente',
        'concluida' => 'Concluída',
        'falha'     => 'Falha',
    ];

    public function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Grid::make(2)->schema([
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
            ]),
            Forms\Components\TextInput::make('tx_hash')
                ->label('Hash da transação')
                ->maxLength(130)
                ->unique(ignoreRecord: true)
                ->placeholder('0x...'),
            Forms\Components\Textarea::make('notes')
                ->label('Observações')
                ->rows(2),
        ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('tx_hash')
            ->columns([
                Tables\Columns\TextColumn::make('type')
                    ->label('Tipo')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'deposito' => 'success',
                        'saque'    => 'danger',
                        'trade'    => 'info',
                        default    => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => self::TYPES[$state] ?? $state),
                Tables\Columns\TextColumn::make('amount')
                    ->label('Valor')
                    ->formatStateUsing(fn (Transaction $record): string => number_format((float) $record->amount, 8))
                    ->summarize(Sum::make()->label('Total')
                        ->formatStateUsing(fn ($state): string => number_format((float) $state, 8))),
                Tables\Columns\TextColumn::make('fee')
                    ->label('Taxa')
                    ->formatStateUsing(fn (Transaction $record): string => number_format((float) $record->fee, 8)),
                Tables\Columns\TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'concluida' => 'success',
                        'pendente'  => 'warning',
                        'falha'     => 'danger',
                        default     => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => self::STATUSES[$state] ?? $state),
                Tables\Columns\TextColumn::make('tx_hash')
                    ->label('Hash')
                    ->copyable()
                    ->limit(16)
                    ->toggleable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Data')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make(),
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
                        Notification::make()->title('Saque aprovado')->success()->send();
                    }),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }
}
