<?php

namespace App\Filament\Pages;

use App\Filament\Resources\ApplicationResource\Widgets\ApplicationByAgeChart;
use App\Filament\Resources\ApplicationResource\Widgets\ApplicationGenderChart;
use App\Filament\Resources\ApplicationResource\Widgets\ApplicationsByDayChart;
use App\Filament\Resources\ApplicationResource\Widgets\ApplicationsByLanguageChart;
use App\Filament\Resources\ApplicationResource\Widgets\ApplicationsByNationalityChart;
use App\Filament\Resources\ApplicationResource\Widgets\ApplicationsByRegionChart;
use App\Filament\Resources\ApplicationResource\Widgets\ApplicationsBySourceChart;
use App\Filament\Resources\ApplicationResource\Widgets\ApplicationsByStatusChart;
use BezhanSalleh\FilamentShield\Traits\HasPageShield;
use Filament\Pages\Page;

class DefaultPage extends Page
{
    use HasPageShield;

    protected static ?string $navigationIcon = 'heroicon-o-presentation-chart-line';

    protected static string $view = 'filament.pages.my-custom-dashboard-page';
    protected static ?string $navigationLabel = 'Statistikalar';
    protected static ?string $title = 'Statistika';


    protected function getWidgets(): array
    {
        return [
            ApplicationsByDayChart::class,
            ApplicationsByStatusChart::class,
            ApplicationsByRegionChart::class,
            ApplicationsByNationalityChart::class,
            ApplicationsByLanguageChart::class,
            ApplicationsBySourceChart::class,
            ApplicationByAgeChart::class,
            ApplicationGenderChart::class,
        ];
    }






}
