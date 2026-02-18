<?php

namespace App\Filament\Resources\ApplicationResource\Widgets;

use App\Enum\Lang;
use Illuminate\Support\Facades\DB;
use Leandrocfe\FilamentApexCharts\Widgets\ApexChartWidget;

class ApplicationsByLanguageChart extends ApexChartWidget
{
    /**
     * Chart Id
     *
     * @var string
     */
    protected static ?string $chartId = 'applicationsByLanguageChart';

    /**
     * Widget Title
     *
     * @var string|null
     */
    protected static ?string $heading = "Kandidatlar til bilish bo'yicha";

    /**
     * Chart options (series, labels, types, size, animations...)
     * https://apexcharts.com/docs/options
     *
     * @return array
     */
    protected function getOptions(): array
    {
        // Count rows in `languages` table grouped by language code
        $rows = DB::table('languages')
            ->select('name', DB::raw('COUNT(*) as total'))
            ->groupBy('name')
            ->get()
            ->pluck('total', 'name')
            ->map(fn ($v) => (int) $v)
            ->toArray();

        // Build categories from enum (stable order, includes zeros)
        $codes = Lang::getValues();               // ['uz','ru','en']
        $categories = array_map(fn ($c) => Lang::name($c), $codes);
        $seriesData = array_map(fn ($c) => $rows[$c] ?? 0, $codes);

        return [
            'chart' => [
                'type' => 'bar',
                'height' => 380,
            ],
            'series' => [
                [
                    'name' => 'Til soni',
                    'data' => $seriesData,
                ],
            ],
            'xaxis' => [
                'categories' => $categories,
                'labels' => [
                    'rotate' => -10,
                    'style' => ['fontFamily' => 'inherit', 'fontSize' => '12px'],
                ],
            ],
            'yaxis' => [
                'title' => ['text' => 'Kandidatlar soni'],
            ],
            'plotOptions' => [
                'bar' => [
                    'horizontal' => false,
                    'columnWidth' => '50%',
                    'borderRadius' => 4,
                ],
            ],
            'dataLabels' => ['enabled' => false],
            'stroke' => ['show' => true, 'width' => 2],
            'tooltip' => ['enabled' => true],
            'grid' => ['strokeDashArray' => 3],
            'colors' => ['#06b6d4'],
        ];
    }
}
