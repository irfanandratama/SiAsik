<?php

namespace App\Traits;

trait GoToListTrait
{
    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
