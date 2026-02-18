<?php

namespace App\Filament\Resources\StatusResource\Pages;

use App\Filament\Resources\StatusResource;
use Filament\Actions;
use Filament\Actions\Action;
use Filament\Resources\Pages\EditRecord;

class EditStatus extends EditRecord
{
    protected static string $resource = StatusResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make()
                ->label("O'chirish"),
        ];
    }

    public function getTitle(): string
    {
        return 'Statusni tahrirlash';
    }

    protected function getFormActions(): array
    {
        return [
            Action::make('save')
                ->label('Saqlash')
                ->color('success')
                ->submit('save'),

            Action::make('cancel')
                ->label('Bekor qilish')
                ->color('primary')
                ->url(StatusResource::getUrl())
        ];
    }
}
