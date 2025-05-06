<?php

use App\Livewire\ReportingForm;
use App\Filament\Public\Pages\CleaningForm;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/lapor/kebersihan', CleaningForm::class);
