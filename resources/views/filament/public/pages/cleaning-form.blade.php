<x-filament::card>
<div class="fi-layout min-h-screen max-h-max p-8 max-w-5x1 mx-auto mb-5">
    <div class="justify-center items-center mx-auto p-4">
        <x-filament::section>
            <x-slot name="heading">
                <div class="grid justify-center items-center mx-auto">
                    SiAsik
                </div>
            </x-slot>
            <x-slot name="description">
                <div class="mx-auto text-sm text-gray-500 dark: text-gray-400">
                    Sistem Aplikasi Kebersihan. 
                </div>
            </x-slot>
        </x-filament::section>
        <x-filament-panels::form wire:submit="create">
        {{ $this->form }}

        <x-filament::button type="submit">
            Submit
        </x-filament::button>
        <x-filament::button color="gray" href="{{ config('app.url')}}" tag="a">
            Cancel
        </x-filament::button>
    </x-filament-panels::form>

    <x-filament-actions::modals />
    </div>
    
</div>
</x-filament::card>

