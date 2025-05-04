<?php

namespace App\Filament\Public\Pages;

use App\Models\Reporting;
use Filament\Forms;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Notification;
use Filament\Pages\BasePage;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Illuminate\Database\Eloquent\Builder;

class CleaningForm extends BasePage implements HasForms
{
    use InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static string $view = 'filament.public.pages.cleaning-form';

    public ?array $data = [];

    public function mount(): void
    {
        $req = request();
        $this->form->fill($req->query());
        $this->data['status_id'] = 1; // initial status
        $this->data['created_by'] = 0; // non user internal
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('informer_name')                    
                    ->afterStateUpdated(fn (Set $set, $state) => $set('informer_name', $state))
                    ->label('Nama')
                    ->placeholder('Isi dengan nama Anda'),
                Forms\Components\Select::make('room_id')
                    ->relationship('room', 'name')
                    ->preload()
                    ->live()
                    ->required()
                    // ->afterStateUpdated(fn (Set $set) => $set('assign_to', []))
                    ->afterStateUpdated(function (Select $component) {
                        $select = $component->getContainer()->getComponent('petugas');
                        $select->state(array_key_first($select->getOptions()));
                    })
                    ->label('Ruangan')
                    ->placeholder('Pilih ruangan')
                    ->disabled()
                    ->dehydrated(),
                Forms\Components\Select::make('assign_to')
                    ->key('petugas')
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
                    ->label(__('Petugas Kebersihan'))
                    ->placeholder('Pilih petugas kebersihan'),
                Forms\Components\Select::make('condition_id')
                    ->relationship('condition', 'name')
                    ->required()
                    ->preload()
                    ->searchable(),
                Forms\Components\Textarea::make('description')
                    ->required()
                    ->disabledOn('edit')
                    ->columnSpanFull()
                    ->label(__('Keterangan')),
                Forms\Components\Hidden::make('status_id'),
            ])
            ->statePath('data')
            ->model(Reporting::class);
    }

    public function create(): void
    {
        try {
            $data = $this->form->getState();

            $record = Reporting::create($data);

            $this->form->model($record)->saveRelationships();

            Notification::make()
                ->title(__('Saved successfully'))
                ->success()
                ->send();

            $this->form->fill();

            $value = env('APP_URL', 'http://localhost:8000/admin');

            redirect($value);

        } catch (\Throwable $th) {
            return;
        }
        
    }
}
