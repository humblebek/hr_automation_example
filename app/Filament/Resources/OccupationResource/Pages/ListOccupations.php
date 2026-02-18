<?php

namespace App\Filament\Resources\OccupationResource\Pages;

use App\Filament\Resources\OccupationResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListOccupations extends ListRecords
{
    protected static string $resource = OccupationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->label("Yangi bo'sh ish o'rni qo'shish "),
        ];
    }

    public function getTitle(): string
    {
        return " Bo'sh ish o'rinlari ro'yxati";
    }
}
