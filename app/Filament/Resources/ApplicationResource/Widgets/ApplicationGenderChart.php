<?php

namespace App\Filament\Resources\ApplicationResource\Widgets;

use App\Enum\Gender;
use App\Models\Application;
use Leandrocfe\FilamentApexCharts\Widgets\ApexChartWidget;

class ApplicationGenderChart extends ApexChartWidget
{
    /**
     * Chart Id
     *
     * @var string
     */
    protected static ?string $chartId = 'applicationGenderChart';

    /**
     * Widget Title
     *
     * @var string|null
     */
    protected static ?string $heading = 'Jins bo‘yicha kandidatlar';

    /**
     * Chart options (series, labels, types, size, animations...)
     * https://apexcharts.com/docs/options
     *
     * @return array
     */
    protected function getOptions(): array
    {
        $genderCounts = Application::select('sex')
            ->selectRaw('COUNT(*) as total')
            ->groupBy('sex')
            ->pluck('total', 'sex')
            ->toArray();

        // Dynamic labels and values from enum
        $labels = [];
        $series = [];

        foreach (Gender::cases() as $gender) {
            $labels[] = $gender->label();
            $series[] = $genderCounts[$gender->value] ?? 0;
        }

        return [
            'chart' => [
                'type' => 'pie',
                'height' => 300,
            ],
            'series' => $series,
            'labels' => $labels,
            'colors' => ['#3b82f6', '#f472b6'],
            'legend' => [
                'position' => 'bottom',
            ],
            'dataLabels' => [
                'enabled' => true,
            ],
        ];
    }
}
