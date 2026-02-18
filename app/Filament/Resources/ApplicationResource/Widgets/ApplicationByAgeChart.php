<?php

namespace App\Filament\Resources\ApplicationResource\Widgets;

use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Support\Facades\DB;
use Leandrocfe\FilamentApexCharts\Widgets\ApexChartWidget;

class ApplicationByAgeChart extends ApexChartWidget
{

    public function getChartId(): ?string
    {
        return 'applicationByAgeChart';
    }

    public function getHeading(): string|Htmlable|null
    {
        return 'Yosh toifalari bo‘yicha kandidatlar';
    }

    protected function getOptions(): array
    {
        // PostgreSQL-compatible query
        $ageData = DB::table('applications')
            ->select(
                'applications.id',
                DB::raw('FLOOR(EXTRACT(YEAR FROM AGE(CURRENT_DATE, birth_date)))::int AS age')
            )
            ->whereNotNull('birth_date')
            ->get()
            ->map(function ($item) {
                $item->age_group = match (true) {
                    $item->age >= 15 && $item->age <= 20 => '15-20',
                    $item->age >= 21 && $item->age <= 30 => '21-30',
                    $item->age >= 31 && $item->age <= 40 => '31-40',
                    $item->age > 40                       => '40+',
                    default                              => null,
                };
                return $item;
            });

        // Group and count per age group
        $groupedData = $ageData
            ->filter(fn($item) => $item->age_group !== null)
            ->groupBy('age_group')
            ->map(fn($group) => ['count' => $group->count()]);

        $result = $groupedData->toArray();

        $series = array_map(fn($item) => $item['count'], array_values($result));
        $labels = array_keys($result);

        return [
            'chart' => [
                'type' => 'pie', // Change to 'bar' if you prefer bars
                'height' => 300,
            ],
            'series' => $series,
            'labels' => $labels,
            'legend' => [
                'labels' => [
                    'fontFamily' => 'inherit',
                ],
            ],
            'colors' => ['#60a5fa', '#34d399', '#fbbf24', '#f87171'], // optional: add nice color palette
        ];
    }

}
