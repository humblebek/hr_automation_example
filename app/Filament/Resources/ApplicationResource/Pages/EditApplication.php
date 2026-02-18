<?php

namespace App\Filament\Resources\ApplicationResource\Pages;

use App\Filament\Resources\ApplicationResource;
use App\Filament\Resources\DepartmentResource;
use Filament\Actions;
use Filament\Actions\Action;
use Filament\Resources\Pages\EditRecord;

class EditApplication extends EditRecord
{
    protected static string $resource = ApplicationResource::class;

    protected function getHeaderActions(): array
    {
        return [
//            Actions\DeleteAction::make()
//                ->label("O'chirish"),
        ];
    }

    public function getTitle(): string
    {
        return 'Kandidatni tahrirlash';
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
                ->url(ApplicationResource::getUrl())
        ];
    }
}
