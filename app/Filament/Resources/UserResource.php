<?php

namespace App\Filament\Resources;

use App\Filament\Resources\UserResource\Pages;
use App\Filament\Resources\UserResource\RelationManagers;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class UserResource extends Resource
{
    protected static ?string $model = User::class;

    protected static ?string $navigationIcon = 'heroicon-o-user';

    protected static ?string $navigationGroup = 'Master Menu';

    public static function shouldRegisterNavigation(): bool
    {
        return auth()->user()->hasRole('super_admin');
    }

    public static function canAccess(): bool 
    { 
        return auth()->user()->hasRole('super_admin'); 
    } 

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                                            ->required() // cannot empty
                                            ->maxLength(255), // max char 255
                Forms\Components\TextInput::make('email')
                                            ->required() // cannot empty
                                            ->email() // email validation
                                            ->maxLength(255), // max char 255
                Forms\Components\TextInput::make('password')
                                            ->required() // cannot empty
                                            ->password() //  password text input
                                            ->revealable() // hide show password
                                            ->maxLength(255) // max char 255
                                            ->dehydrated(fn ($state) => filled($state)) // don't update when empty
                                            ->required(fn (string $context): bool => $context === 'create'), // required only in create
                Forms\Components\Select::make('roles')
                                            ->relationship('roles','name')
                                            ->preload()
                                            ->searchable(),
                Forms\Components\Select::make('room_id')
                                            ->multiple()
                                            ->relationship('room', 'name')
                                            ->preload()
                                            ->searchable(),
                Forms\Components\TextInput::make('phone_number')
                                            ->tel()
                                            ->telRegex('/^[+]*[(]{0,1}[0-9]{1,4}[)]{0,1}[-\s\.\/0-9]*$/'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')->searchable(),
                Tables\Columns\TextColumn::make('email')->searchable(),
                Tables\Columns\TextColumn::make('roles.name')->label('Role')->searchable(),
                Tables\Columns\TextColumn::make('room.name')->label('Room')->searchable(),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
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
            'index' => Pages\ListUsers::route('/'),
            'create' => Pages\CreateUser::route('/create'),
            'edit' => Pages\EditUser::route('/{record}/edit'),
        ];
    }
}
