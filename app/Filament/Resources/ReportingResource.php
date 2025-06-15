<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ReportingResource\Pages;
use App\Filament\Resources\ReportingResource\RelationManagers;
use App\Mail\NewReport;
use App\Models\Reporting;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Tables;
use Filament\Tables\Actions\Action;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Mail;
use Filament\Notifications\Notification;

class ReportingResource extends Resource
{
    protected static ?string $model = Reporting::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function getModelLabel(): string
    {
        return __('reporting.title');
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('informer')
                    ->disabledOn('edit')
                    // ->disabled(fn(): bool =>! auth()->user()->hasRole('super_admin'))
                    ->relationship('informer_i','name')
                    ->preload()
                    ->live()
                    ->afterStateUpdated(fn (Set $set, $state) => $set('informer_name', User::firstWhere('id', $state)->name))
                    ->label(__('reporting.field.informer'))
                    ->hiddenOn('edit'),
                Forms\Components\TextInput::make('informer_name')
                    ->hiddenOn('create')
                    ->disabled()
                    ->dehydrated(),
                Forms\Components\Select::make('room_id')
                    ->disabledOn('edit')
                    ->relationship('room', 'name')
                    ->preload()
                    ->searchable()
                    ->live()
                    ->afterStateUpdated(fn (Set $set) => $set('assign_to', []))
                    ->label(__('reporting.field.room')),
                Forms\Components\Select::make('assign_to')
                    ->relationship(
                        name: 'assign',
                        titleAttribute: 'name', 
                        modifyQueryUsing: fn (Builder $query, Get $get) => $query->join('room_users', 'users.id', '=', 
                        'room_users.user_id')->join('rooms', 'rooms.id', '=', 
                        'room_users.room_id')->where('room_users.room_id', $get('room_id')),
                    )
                    ->searchable()
                    ->live()
                    ->preload()
                    // ->afterStateUpdated(fn (Set $set, ?string $state) => $set('status_id', 3))
                    // ->disabled(fn(): bool => (auth()->user()->hasRole('cleaner') || auth()->user()->hasRole('pegawai')))
                    // ->required(fn(): bool => (auth()->user()->hasRole('super_admin') || auth()->user()->hasRole('kepala_sub_bagian')))
                    ->label(__('reporting.field.assign_to'))
                    ->selectablePlaceholder(false),
                Forms\Components\Select::make('condition_id')
                    ->relationship('condition', 'name')
                    ->required()
                    ->preload()
                    ->searchable()
                    ->live()
                    ->afterStateUpdated(fn (Set $set, ?string $state) => $set('status_id', 4))
                    ->label(__('reporting.field.condition')),
                Forms\Components\Textarea::make('description')
                    ->required()
                    ->disabledOn('edit')
                    ->columnSpanFull()
                    ->label(__('reporting.field.description')),
                Forms\Components\Select::make('status_id')
                    ->disabled()
                    ->dehydrated()
                    ->relationship('status','name')
                    ->columns([
                        'sm' => 1,
                        'xl' => 2,
                        '2xl' => 2,
                    ])
                    ->required()
                    ->default(1)->label(__('reporting.field.status')),
                Forms\Components\Textarea::make('approval_message')
                    ->hidden(fn (Get $get): bool => ! $get('approval_message'))
                    ->disabled()
                    ->columnSpanFull()
                    ->label(__('reporting.field.approval_message')),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('informer_name')->label(__('reporting.column.informer'))->searchable(),
                Tables\Columns\TextColumn::make('assign.name')->label(__('reporting.column.assign_to'))->searchable(),
                Tables\Columns\TextColumn::make('room.name')->label(__('reporting.column.room'))->searchable(),
                Tables\Columns\TextColumn::make('condition.name')->label(__('reporting.column.condition'))->searchable(),
                Tables\Columns\TextColumn::make('status.name')->label(__('reporting.column.status'))->searchable(),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make()
                    ->hidden(fn($record) => ($record->status->id == 4 || $record->status->id == 3)),
                Action::make('Setuju')
                    ->fillForm(fn (Reporting $record): array => [
                        'room_id' => $record->room->id,
                        'assign_to' => $record->assign->id,
                        'condition_id' => $record->condition->id,
                        'description' => $record->description,
                    ])
                    ->form([
                        Forms\Components\Select::make('room_id')
                            ->relationship('room', 'name')
                            ->label(__('reporting.field.room'))
                            ->disabled(),
                        Forms\Components\Select::make('assign_to')
                            ->relationship('assign', 'name')
                            ->label(__('reporting.field.assign_to'))
                            ->disabled(),
                        Forms\Components\Select::make('condition_id')
                            ->relationship('condition', 'name')
                            ->label(__('reporting.field.condition'))
                            ->disabled(),
                        Forms\Components\Textarea::make('description')
                            ->label(__('reporting.field.description'))
                            ->disabled(),
                        Forms\Components\Textarea::make('approval_message')
                            ->label(__('reporting.field.approval_message')),
                    ])
                    // ->disabledForm()
                    ->action(function (Reporting $record, array $data): void {

                        $record->status_id = 3;
                        $record->approval_message = $data['approval_message'];
                        $record->save();

                        $cleaner = User::find($record->assign_to);
                        Notification::make()
                            ->title('Pelaksanaan kebersihan kamu sudah disetujui.')
                            ->icon('heroicon-s-ticket')
                            ->body("Selamat! Pelaksanaan kebersihan kamu sudah disetujui.")
                            ->sendToDatabase($cleaner);
                        $maildata = [
                            'name' => $cleaner->name,
                            'message' => "Selamat! Pelaksanaan kebersihan kamu sudah disetujui. Silakan membuka aplikasi untuk melihat lebih detail."
                        ];
                        Mail::to($cleaner->email)->send(new NewReport($maildata));
                    })
                    ->visible(function (Reporting $record) {
                        if ($record->assign_to && $record->status_id == 4) {
                            return true;
                        } else {
                            return false;
                        }
                        
                    })
                    ->modalSubmitActionLabel('Setuju'),
                Action::make('Tolak')
                    ->fillForm(fn (Reporting $record): array => [
                        'room_id' => $record->room->id,
                        'assign_to' => $record->assign->id,
                        'condition_id' => $record->condition->id,
                        'description' => $record->description,
                    ])
                    ->form([
                        Forms\Components\Select::make('room_id')
                            ->relationship('room', 'name')
                            ->label(__('reporting.field.room'))
                            ->disabled(),
                        Forms\Components\Select::make('assign_to')
                            ->relationship('assign', 'name')
                            ->label(__('reporting.field.assign_to'))
                            ->disabled(),
                        Forms\Components\Select::make('condition_id')
                            ->relationship('condition', 'name')
                            ->label(__('reporting.field.condition'))
                            ->disabled(),
                        Forms\Components\Textarea::make('description')
                            ->label(__('reporting.field.description'))
                            ->disabled(),
                        Forms\Components\Textarea::make('approval_message')
                            ->label(__('reporting.field.approval_message')),
                    ])
                    // ->disabledForm()
                    ->action(function (Reporting $record, array $data): void {
                        $record->status_id = 2;
                        $record->condition_id = 2;
                        $record->approval_message = $data['approval_message'];
                        $record->save();

                        $cleaner = User::find($record->assign->id);
                        Notification::make()
                            ->title('Pelaksanaan kebersihan kamu ditolak.')
                            ->icon('heroicon-s-ticket')
                            ->body("Sayang sekali. Pelaksanaan kebersihan kamu ditolak. Silakan cek pesan penolakan pada menu Pelaporan Kebersihan.")
                            // ->actions([
                            //     Action::make('Lihat')
                            //         ->url(TicketResource::getUrl('view', ['record' => $ticket->id]))
                            //         ->button()
                            //         ->markAsRead(),
                            // ])
                            ->sendToDatabase($cleaner);
                        $maildata = [
                            'name' => $cleaner->name,
                            'message' => "Sayang sekali. Pelaksanaan kebersihan kamu ditolak. Silakan cek pesan penolakan pada menu Pelaporan Kebersihan di aplikasi SiAsik."
                        ];
                        Mail::to($cleaner->email)->send(new NewReport($maildata));
                    })
                    ->visible(function (Reporting $record) {
                        if ($record->assign_to && $record->status_id == 4) {
                            return true;
                        } else {
                            return false;
                        }
                        
                    })
                    ->modalSubmitActionLabel('Tolak'),
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
            'index' => Pages\ListReportings::route('/'),
            'create' => Pages\CreateReporting::route('/create'),
            'edit' => Pages\EditReporting::route('/{record}/edit'),
        ];
    }
}
