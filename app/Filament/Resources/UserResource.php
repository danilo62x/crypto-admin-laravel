<?php

namespace App\Filament\Resources;

use App\Filament\Resources\UserResource\Pages;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Infolists;
use Filament\Infolists\Infolist;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\Hash;

class UserResource extends Resource
{
    protected static ?string $model = User::class;
    protected static ?string $recordTitleAttribute = 'name';
    protected static ?string $navigationIcon = 'heroicon-o-users';
    protected static ?string $navigationGroup = 'Conta';
    protected static ?string $navigationLabel = 'Usuários';
    protected static ?string $modelLabel = 'Usuário';
    protected static ?string $pluralModelLabel = 'Usuários';
    protected static ?int $navigationSort = 1;

    protected const ROLES = [
        'admin'  => 'Administrador',
        'editor' => 'Editor',
        'viewer' => 'Visualizador',
    ];

    protected const ROLE_COLORS = [
        'admin'  => 'danger',
        'editor' => 'primary',
        'viewer' => 'gray',
    ];

    protected const KYC_STATUSES = [
        'pendente'   => 'Pendente',
        'verificado' => 'Verificado',
        'recusado'   => 'Recusado',
    ];

    protected const KYC_COLORS = [
        'pendente'   => 'warning',
        'verificado' => 'success',
        'recusado'   => 'danger',
    ];

    public static function getGloballySearchableAttributes(): array
    {
        return ['name', 'email'];
    }

    public static function getNavigationBadge(): ?string
    {
        $pending = static::getModel()::where('kyc_status', 'pendente')->count();
        return $pending > 0 ? (string) $pending : null;
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'warning';
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Dados da conta')
                    ->icon('heroicon-o-user')
                    ->columns(2)
                    ->schema([
                        Forms\Components\FileUpload::make('avatar')
                            ->label('Foto')
                            ->avatar()
                            ->image()
                            ->imageEditor()
                            ->directory('avatars')
                            ->columnSpanFull(),
                        Forms\Components\TextInput::make('name')
                            ->label('Nome')
                            ->required()
                            ->maxLength(120),
                        Forms\Components\TextInput::make('email')
                            ->label('Email')
                            ->email()
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->prefixIcon('heroicon-o-envelope'),
                        Forms\Components\Select::make('role')
                            ->label('Perfil')
                            ->options(self::ROLES)
                            ->default('editor')
                            ->native(false)
                            ->required(),
                        Forms\Components\TextInput::make('phone')
                            ->label('Telefone')
                            ->tel()
                            ->maxLength(30),
                        Forms\Components\Toggle::make('is_active')
                            ->label('Conta ativa')
                            ->default(true)
                            ->helperText('Contas inativas não conseguem acessar o painel.'),
                        Forms\Components\Select::make('kyc_status')
                            ->label('Status KYC')
                            ->options(self::KYC_STATUSES)
                            ->default('pendente')
                            ->native(false)
                            ->required(),
                    ]),
                Forms\Components\Section::make('Segurança')
                    ->icon('heroicon-o-lock-closed')
                    ->schema([
                        Forms\Components\TextInput::make('password')
                            ->label('Senha')
                            ->password()
                            ->revealable()
                            ->required(fn (string $context): bool => $context === 'create')
                            ->dehydrated(fn ($state): bool => filled($state))
                            ->dehydrateStateUsing(fn ($state): string => Hash::make($state))
                            ->minLength(8)
                            ->helperText('Deixe em branco para manter a senha atual.'),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('avatar')
                    ->label('')
                    ->circular()
                    ->defaultImageUrl(fn (User $record): string => 'https://ui-avatars.com/api/?background=465fff&color=fff&name=' . urlencode($record->name)),
                Tables\Columns\TextColumn::make('name')
                    ->label('Usuário')
                    ->description(fn (User $record): string => $record->email)
                    ->searchable(['name', 'email'])
                    ->sortable()
                    ->weight('medium'),
                Tables\Columns\TextColumn::make('role')
                    ->label('Perfil')
                    ->badge()
                    ->color(fn (string $state): string => self::ROLE_COLORS[$state] ?? 'gray')
                    ->formatStateUsing(fn (string $state): string => self::ROLES[$state] ?? $state)
                    ->sortable(),
                Tables\Columns\ToggleColumn::make('is_active')
                    ->label('Ativo'),
                Tables\Columns\TextColumn::make('kyc_status')
                    ->label('KYC')
                    ->badge()
                    ->color(fn (string $state): string => self::KYC_COLORS[$state] ?? 'gray')
                    ->formatStateUsing(fn (string $state): string => self::KYC_STATUSES[$state] ?? $state),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Criado')
                    ->dateTime('d/m/Y')
                    ->sortable()
                    ->toggleable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('role')
                    ->label('Perfil')
                    ->options(self::ROLES),
                Tables\Filters\SelectFilter::make('kyc_status')
                    ->label('Status KYC')
                    ->options(self::KYC_STATUSES),
                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('Conta ativa'),
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
            ->defaultSort('created_at', 'desc');
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Infolists\Components\Section::make()
                    ->schema([
                        Infolists\Components\Split::make([
                            Infolists\Components\ImageEntry::make('avatar')
                                ->label('')
                                ->circular()
                                ->defaultImageUrl(fn (User $record): string => 'https://ui-avatars.com/api/?background=465fff&color=fff&size=128&name=' . urlencode($record->name))
                                ->grow(false),
                            Infolists\Components\Grid::make(2)->schema([
                                Infolists\Components\TextEntry::make('name')->label('Nome')->size('lg')->weight('bold'),
                                Infolists\Components\TextEntry::make('role')
                                    ->label('Perfil')
                                    ->badge()
                                    ->color(fn (string $state): string => self::ROLE_COLORS[$state] ?? 'gray')
                                    ->formatStateUsing(fn (string $state): string => self::ROLES[$state] ?? $state),
                                Infolists\Components\TextEntry::make('email')->label('Email')->icon('heroicon-o-envelope')->copyable(),
                                Infolists\Components\TextEntry::make('phone')->label('Telefone')->placeholder('—'),
                                Infolists\Components\IconEntry::make('is_active')->label('Conta ativa')->boolean(),
                                Infolists\Components\TextEntry::make('kyc_status')
                                    ->label('KYC')
                                    ->badge()
                                    ->color(fn (string $state): string => self::KYC_COLORS[$state] ?? 'gray')
                                    ->formatStateUsing(fn (string $state): string => self::KYC_STATUSES[$state] ?? $state),
                                Infolists\Components\TextEntry::make('created_at')->label('Criado em')->dateTime('d/m/Y H:i'),
                            ]),
                        ])->from('md'),
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
            'index'  => Pages\ListUsers::route('/'),
            'create' => Pages\CreateUser::route('/create'),
            'view'   => Pages\ViewUser::route('/{record}'),
            'edit'   => Pages\EditUser::route('/{record}/edit'),
        ];
    }
}
