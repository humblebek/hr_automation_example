<?php

namespace App\Filament\Resources\OccupationResource\Pages;

use App\Filament\Resources\OccupationResource;
use Filament\Actions;
use Filament\Actions\Action;
use Filament\Resources\Pages\EditRecord;

class EditOccupation extends EditRecord
{
    protected static string $resource = OccupationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make()
                ->label("O'chirish"),
        ];
    }

    public function getTitle(): string
    {
        return "Bo'sh ish o'rnini tahrirlash";
    }

    protected function getFormActions(): array
    {
        return [
            Action::make('save')
                ->label('Saqlash')
                ->color('success')
                ->submit('save'),

            Action::make('cancel')
                ->label('Ortga')
                ->color('primary')
                ->url(OccupationResource::getUrl())
        ];
    }
}
