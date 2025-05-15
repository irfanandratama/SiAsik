<?php

namespace App\Filament\Resources\ConditionResource\Pages;

use App\Filament\Resources\ConditionResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Mail;
use App\Mail\TestingEmail;

class CreateCondition extends CreateRecord
{
    protected static string $resource = ConditionResource::class;

    protected function getRedirectUrl(): string
    {
        $data = [
            'name' => 'John Doe',
            'message' => 'This is a test email from Laravel 12.'
        ];
        Mail::to('drama2713@gmail.com')->send(new TestingEmail($data));

        return $this->getResource()::getUrl('index');
    }
}
