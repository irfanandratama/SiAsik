<?php

namespace App\Filament\Resources\ConditionResource\Pages;

use App\Filament\Resources\ConditionResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Mail;
use App\Mail\TestingEmail;
use Filament\Notifications\Notification;

class CreateCondition extends CreateRecord
{
    protected static string $resource = ConditionResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function afterCreate(): void
    {
        // Runs after the form fields are saved to the database.
     
       // $data = [
        //     'name' => 'John Doe',
        //     'message' => 'This is a test email from Laravel 12.'
        // ];
        // Mail::to('drama2713@gmail.com')->send(new TestingEmail($data));

        $recipient = auth()->user();
        
        $send = Notification::make()
        ->title('Saved successfully')
        ->sendToDatabase($recipient);

    }
}
