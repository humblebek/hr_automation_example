<?php

namespace App\Filament\Resources\OccupationResource\Pages;

use App\Filament\Resources\OccupationResource;
use Filament\Actions\Action;
use Filament\Resources\Pages\CreateRecord;

class CreateOccupation extends CreateRecord
{
    protected static string $resource = OccupationResource::class;

    public function getTitle(): string
    {
        return "Yangi bo'sh ish o'rni qo‘shish";
    }

    protected function getFormActions(): array
    {
        return [
            Action::make('create')
                ->label("Qo'shish")
                ->color('success')
                ->submit('create'),

            Action::make('cancel')
                ->label('Ortga')
                ->color('danger')
                ->url(OccupationResource::getUrl())
        ];
    }
}
