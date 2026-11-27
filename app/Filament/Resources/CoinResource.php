<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CoinResource\Pages;
use App\Models\Coin;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Infolists;
use Filament\Infolists\Infolist;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\Summarizers\Average;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class CoinResource extends Resource
{
    protected static ?string $model = Coin::class;
    protected static ?string $recordTitleAttribute = 'name';
    protected static ?string $navigationIcon = 'heroicon-o-currency-dollar';
    protected static ?string $navigationGroup = 'Cadastros';
    protected static ?string $navigationLabel = 'Moedas';
    protected static ?string $modelLabel = 'Moeda';
    protected static ?string $pluralModelLabel = 'Moedas';
    protected static ?int $navigationSort = 1;

    public static function getGloballySearchableAttributes(): array
    {
        return ['symbol', 'name'];
    }

    public static function getGlobalSearchResultDetails($record): array
    {
        return [
            'Símbolo' => $record->symbol,
            'Preço'   => 'R$ ' . number_format((float) $record->price, 2, ',', '.'),
            'Ativa'   => $record->is_active ? 'Sim' : 'Não',
        ];
    }

    public static function getNavigationBadge(): ?string
    {
        return (string) static::getModel()::where('is_active', true)->count();
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'success';
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Group::make()
                    ->schema([
                        Forms\Components\Section::make('Identificação')
                            ->description('Símbolo, nome e detalhes do ativo digital.')
                            ->icon('heroicon-o-currency-dollar')
                            ->schema([
                                Forms\Components\Grid::make(2)->schema([
                                    Forms\Components\TextInput::make('symbol')
                                        ->label('Símbolo')
                                        ->required()
                                        ->unique(ignoreRecord: true)
                                        ->maxLength(20)
                                        ->placeholder('BTC')
                                        ->helperText('Ticker do ativo, ex: BTC, ETH.'),
                                    Forms\Components\TextInput::make('name')
                                        ->label('Nome')
                                        ->required()
                                        ->maxLength(120)
                                        ->placeholder('Bitcoin'),
                                ]),
                                Forms\Components\TextInput::make('logo_url')
                                    ->label('URL do logo')
                                    ->url()
                                    ->maxLength(500)
                                    ->columnSpanFull()
                                    ->helperText('Link público para a imagem do logo (PNG/SVG).'),
                                Forms\Components\RichEditor::make('description')
                                    ->label('Descrição')
                                    ->toolbarButtons(['bold', 'italic', 'bulletList', 'orderedList', 'link', 'h2', 'h3'])
                                    ->columnSpanFull(),
                            ]),
                    ])
                    ->columnSpan(['lg' => 2]),

                Forms\Components\Group::make()
                    ->schema([
                        Forms\Components\Section::make('Cotação')
                            ->icon('heroicon-o-chart-bar-square')
                            ->schema([
                                Forms\Components\TextInput::make('price')
                                    ->label('Preço atual (BRL)')
                                    ->numeric()
                                    ->prefix('R$')
                                    ->required()
                                    ->minValue(0)
                                    ->step(0.00000001),
                            ]),
                        Forms\Components\Section::make('Publicação')
                            ->icon('heroicon-o-eye')
                            ->schema([
                                Forms\Components\Toggle::make('is_active')
                                    ->label('Ativo no exchange')
                                    ->default(true)
                                    ->helperText('Desative para suspender negociação.'),
                            ]),
                    ])
                    ->columnSpan(['lg' => 1]),
            ])
            ->columns(3);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('logo_url')
                    ->label('')
                    ->circular()
                    ->size(40)
                    ->defaultImageUrl(fn (Coin $record): string => 'https://ui-avatars.com/api/?background=f59e0b&color=fff&bold=true&name=' . urlencode($record->symbol)),
                Tables\Columns\TextColumn::make('symbol')
                    ->label('Símbolo')
                    ->description(fn (Coin $record): string => $record->name)
                    ->searchable()
                    ->sortable()
                    ->weight('bold')
                    ->badge()
                    ->color('warning'),
                Tables\Columns\TextColumn::make('name')
                    ->label('Nome')
                    ->searchable()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('price')
                    ->label('Preço (BRL)')
                    ->money('BRL')
                    ->sortable()
                    ->summarize(Average::make()->label('Média')->money('BRL')),
                Tables\Columns\ToggleColumn::make('is_active')
                    ->label('Ativa'),
                Tables\Columns\TextColumn::make('wallets_count')
                    ->label('Carteiras')
                    ->counts('wallets')
                    ->badge()
                    ->color('info')
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Criada')
                    ->dateTime('d/m/Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('Ativa no exchange'),
                Tables\Filters\Filter::make('price')
                    ->form([
                        Forms\Components\TextInput::make('price_from')->label('Preço de')->numeric()->prefix('R$'),
                        Forms\Components\TextInput::make('price_to')->label('Preço até')->numeric()->prefix('R$'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when($data['price_from'], fn (Builder $q, $v): Builder => $q->where('price', '>=', $v))
                            ->when($data['price_to'], fn (Builder $q, $v): Builder => $q->where('price', '<=', $v));
                    })
                    ->indicateUsing(function (array $data): array {
                        $indicators = [];
                        if ($data['price_from'] ?? null) {
                            $indicators[] = 'A partir de R$ ' . $data['price_from'];
                        }
                        if ($data['price_to'] ?? null) {
                            $indicators[] = 'Até R$ ' . $data['price_to'];
                        }
                        return $indicators;
                    }),
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
                Tables\Actions\Action::make('toggleActive')
                    ->label(fn (Coin $record): string => $record->is_active ? 'Suspender' : 'Reativar')
                    ->icon(fn (Coin $record): string => $record->is_active ? 'heroicon-o-pause-circle' : 'heroicon-o-play-circle')
                    ->color(fn (Coin $record): string => $record->is_active ? 'warning' : 'success')
                    ->requiresConfirmation()
                    ->action(function (Coin $record): void {
                        $record->update(['is_active' => ! $record->is_active]);
                        Notification::make()
                            ->title($record->is_active ? 'Moeda reativada' : 'Moeda suspensa')
                            ->success()
                            ->send();
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
                    Tables\Actions\BulkAction::make('activate')
                        ->label('Ativar selecionadas')
                        ->icon('heroicon-o-check-circle')
                        ->color('success')
                        ->action(fn ($records) => $records->each->update(['is_active' => true]))
                        ->deselectRecordsAfterCompletion()
                        ->after(fn () => Notification::make()->title('Moedas ativadas')->success()->send()),
                    Tables\Actions\BulkAction::make('deactivate')
                        ->label('Suspender selecionadas')
                        ->icon('heroicon-o-x-circle')
                        ->color('warning')
                        ->requiresConfirmation()
                        ->action(fn ($records) => $records->each->update(['is_active' => false]))
                        ->deselectRecordsAfterCompletion(),
                    Tables\Actions\DeleteBulkAction::make(),
                    Tables\Actions\RestoreBulkAction::make(),
                    Tables\Actions\ForceDeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('symbol', 'asc')
            ->striped();
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Infolists\Components\Split::make([
                    Infolists\Components\Section::make()
                        ->schema([
                            Infolists\Components\TextEntry::make('name')
                                ->label('Moeda')
                                ->size('lg')
                                ->weight('bold'),
                            Infolists\Components\TextEntry::make('symbol')
                                ->label('Símbolo')
                                ->badge()
                                ->color('warning'),
                            Infolists\Components\TextEntry::make('description')
                                ->label('Descrição')
                                ->html()
                                ->placeholder('—'),
                        ]),
                    Infolists\Components\ImageEntry::make('logo_url')
                        ->label('')
                        ->circular()
                        ->height(100)
                        ->defaultImageUrl(fn (Coin $record): string => 'https://ui-avatars.com/api/?background=f59e0b&color=fff&bold=true&size=128&name=' . urlencode($record->symbol))
                        ->grow(false),
                ])->from('md'),
                Infolists\Components\Section::make('Cotação & status')
                    ->schema([
                        Infolists\Components\TextEntry::make('price')
                            ->label('Preço atual')
                            ->money('BRL')
                            ->weight('bold')
                            ->color('success'),
                        Infolists\Components\IconEntry::make('is_active')
                            ->label('Ativa no exchange')
                            ->boolean(),
                        Infolists\Components\TextEntry::make('wallets_count')
                            ->label('Carteiras')
                            ->state(fn (Coin $record): int => $record->wallets()->count())
                            ->badge()
                            ->color('info'),
                        Infolists\Components\TextEntry::make('created_at')
                            ->label('Criada em')
                            ->dateTime('d/m/Y H:i'),
                    ])
                    ->columns(4),
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
            'index'  => Pages\ListCoins::route('/'),
            'create' => Pages\CreateCoin::route('/create'),
            'view'   => Pages\ViewCoin::route('/{record}'),
            'edit'   => Pages\EditCoin::route('/{record}/edit'),
        ];
    }
}
