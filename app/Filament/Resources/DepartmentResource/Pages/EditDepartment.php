<?php

namespace App\Filament\Resources\DepartmentResource\Pages;

use App\Filament\Resources\DepartmentResource;
use Filament\Actions;
use Filament\Actions\Action;
use Filament\Resources\Pages\EditRecord;

class EditDepartment extends EditRecord
{
    protected static string $resource = DepartmentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make()
                ->label("O'chirish"),
        ];
    }

    public function getTitle(): string
    {
        return "Bo'limni tahrirlash";
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
                ->url(DepartmentResource::getUrl())
        ];
    }
}
