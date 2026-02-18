<?php

namespace App\Filament\Resources\DepartmentResource\Pages;

use App\Filament\Resources\DepartmentResource;
use Filament\Actions\Action;
use Filament\Resources\Pages\CreateRecord;

class CreateDepartment extends CreateRecord
{
    protected static string $resource = DepartmentResource::class;

    public function getTitle(): string
    {
        return "Yangi bo'lim qo‘shish";
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
                ->url(DepartmentResource::getUrl())
        ];
    }
}
