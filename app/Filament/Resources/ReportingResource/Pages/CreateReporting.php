<?php

namespace App\Filament\Resources\ReportingResource\Pages;

use App\Filament\Resources\ReportingResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateReporting extends CreateRecord
{
    protected static string $resource = ReportingResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['created_by'] = auth()->id();
        $data['status_id'] = 1;
        return $data;
    }
}
