<?php

namespace App\Filament\Public\Pages;

use App\Mail\NewReport;
use App\Models\Reporting;
use App\Models\Room;
use App\Models\User;
use Filament\Actions;
use Filament\Forms;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Pages\BasePage;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Mail;

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
                    ->extraInputAttributes(['wire:key' => Str::random(10)])
                    ->relationship(
                        name: 'assign',
                        titleAttribute: 'name', 
                        modifyQueryUsing: fn (Builder $query, Get $get) => $query->join('room_users', 'users.id', '=', 
                        'room_users.user_id')->join('rooms', 'rooms.id', '=', 
                        'room_users.room_id')->where('room_users.room_id', $get('room_id')),
                    )
                    // ->searchable()
                    ->live()
                    ->preload()
                    ->label(__('Petugas Kebersihan'))
                    ->placeholder('Pilih petugas kebersihan'),
                    // ->selectablePlaceholder(false)
                    // ->afterStateUpdated(fn (Set $set, $state) => $set('assign_to', $state)),
                Forms\Components\Select::make('condition_id')
                    ->relationship('condition', 'name')
                    ->required()
                    ->preload()
                    ->label('Kondisi Saat Ini'),
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
                ->title(__('Laporan Anda sudah terkirim.'))
                ->success()
                ->send();

            $this->form->fill();

            $head_divisions = User::role('kepala_sub_bagian')->get();
            $room = Room::find($data['room_id']);
            foreach ($head_divisions as $head_division) {
                Notification::make()
                ->title("Laporan Kebersihan Baru di ruang {$room->name}")
                ->body("{$data['description']}")
                ->icon('heroicon-s-ticket')
                ->actions([
                    Action::make('Baca')
                        ->button()
                        ->markAsRead()
                ])
                ->sendToDatabase($head_division);

                $maildata = [
                    'name' => $head_division->name,
                    'message' => "Laporan kebersihan baru telah masuk melalui SiAsik di ruang {$room->name} dengan deskripsi {$data['description']}. Silakan membuka aplikasi untuk melihat lebih detail."
                ];
                Mail::to($head_division->email)->send(new NewReport($maildata));
            }

            $cleaner = User::find($data['assign_to']);
            Notification::make()
                ->title("Laporan Kebersihan Baru di ruang {$room->name}")
                ->body("{$data['description']}")
                ->icon('heroicon-s-ticket')
                ->actions([
                    Action::make('Baca')
                        ->button()
                        ->markAsRead()
                ])
                ->sendToDatabase($cleaner);
            
            $maildata = [
                'name' => $cleaner->name,
                'message' => "Laporan kebersihan baru telah masuk melalui SiAsik di ruang {$room->name} dengan deskripsi {$data['description']}. Silakan membuka aplikasi untuk melihat lebih detail."
            ];
            if ($cleaner->email) {
                Mail::to($cleaner->email)->send(new NewReport($maildata));
            }

            $value = env('APP_URL', 'http://localhost:8000/');

            redirect($value);

        } catch (\Throwable $th) {
            return;
        }
        
    }
}
