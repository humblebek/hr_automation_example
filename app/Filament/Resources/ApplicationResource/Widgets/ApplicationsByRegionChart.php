<?php

namespace App\Filament\Resources\ApplicationResource\Widgets;

use Illuminate\Support\Facades\DB;
use Leandrocfe\FilamentApexCharts\Widgets\ApexChartWidget;

class ApplicationsByRegionChart extends ApexChartWidget
{
    /**
     * Chart Id
     *
     * @var string
     */
    protected static ?string $chartId = 'applicationsByRegionChart';

    /**
     * Widget Title
     *
     * @var string|null
     */
    protected static ?string $heading = "Viloyatlar bo'yicha kandidatlar";

    /**
     * Chart options (series, labels, types, size, animations...)
     * https://apexcharts.com/docs/options
     *
     * @return array
     */
    protected function getOptions(): array
    {
        // Fetch counts per region (works on Postgres/MySQL)
        // Expects regions table to have a 'name' column. If different, adjust below.
        $rows = DB::table('applications as a')
            ->join('regions as r', 'r.id', '=', 'a.region_id')
            ->selectRaw('COALESCE(r.name, CONCAT(\'ID #\', r.id)) AS region_name, COUNT(*)::int AS total')
            ->groupBy('region_name')
            ->orderByDesc('total')
            ->limit(15) // show top-15; remove/adjust as you wish
            ->get();

        $categories = $rows->pluck('region_name')->toArray();
        $seriesData = $rows->pluck('total')->toArray();

        return [
            'chart' => [
                'type' => 'bar',
                'height' => 380,
            ],
            'series' => [
                [
                    'name' => 'Arizalar',
                    'data' => $seriesData,
                ],
            ],
            'xaxis' => [
                'categories' => $categories,
                'labels' => [
                    'rotate' => -30,
                    'trim' => true,
                    'style' => [
                        'fontFamily' => 'inherit',
                        'fontSize' => '12px',
                    ],
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
            'colors' => ['#0ea5e9'],
            'tooltip' => ['enabled' => true],
            'grid' => ['strokeDashArray' => 3],
        ];
    }
}
