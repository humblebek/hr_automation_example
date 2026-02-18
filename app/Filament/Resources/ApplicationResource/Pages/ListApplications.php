<?php

namespace App\Filament\Resources\ApplicationResource\Pages;

use App\Filament\Resources\ApplicationResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListApplications extends ListRecords
{
    protected static string $resource = ApplicationResource::class;

    protected function getHeaderActions(): array
    {
        return [
//            Actions\CreateAction::make()
//                ->label("Yangi applikant qo'shish "),
        ];
    }

    public function getTitle(): string
    {
        return 'Kandidatlar ro\'yxati';
    }
}
