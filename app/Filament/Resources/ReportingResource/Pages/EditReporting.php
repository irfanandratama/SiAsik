<?php

namespace App\Filament\Resources\ReportingResource\Pages;

use App\Mail\NewReport;
use App\Models\User;
use App\Filament\Resources\ReportingResource;
use Filament\Actions;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Facades\Mail;

class EditReporting extends EditRecord
{
    protected static string $resource = ReportingResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        $data['updated_by'] = auth()->id();

        return $data;
    }

    protected function beforeFill(): void {
        $record['old_status'] = $this->record['status_id'];
    }

    protected function afterSave(): void
    {
        $reporting = $this->record;

        if ($reporting->old_status != $reporting->status_id) {
            $head_divisions = User::role('kepala_sub_bagian')->get();

            foreach ($head_divisions as $head_division) {

                Notification::make()
                    ->title("Pelaksanaan Kebersihan Perlu Dikonfirmasi")
                    ->body("Pelaksanaan kebersihan di ruangan {$reporting->room->name} oleh {$reporting->assign->name} perlu dikonfirmasi perubahan statusnya.")
                    ->icon('heroicon-s-ticket')
                    ->sendToDatabase($head_division);

                $maildata = [
                    'name' => $head_division->name,
                    'message' => "Pelaksanaan kebersihan di ruangan {$reporting->room->name} oleh {$reporting->assign->name} perlu dikonfirmasi perubahan statusnya. Silakan membuka aplikasi untuk melihat lebih detail."
                ];
                Mail::to($head_division->email)->send(new NewReport($maildata));

            }
        }
    }

    // protected function getHeaderActions(): array
    // {
    //     return [
    //         Actions\DeleteAction::make(),
    //     ];
    // }
}
