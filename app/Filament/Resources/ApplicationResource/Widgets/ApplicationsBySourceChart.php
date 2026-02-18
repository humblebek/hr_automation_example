<?php

namespace App\Filament\Resources\ApplicationResource\Widgets;

use Illuminate\Support\Facades\DB;
use Leandrocfe\FilamentApexCharts\Widgets\ApexChartWidget;

class ApplicationsBySourceChart extends ApexChartWidget
{
    /**
     * Chart Id
     *
     * @var string
     */
    protected static ?string $chartId = 'applicationsBySourceChart';

    /**
     * Widget Title
     *
     * @var string|null
     */
    protected static ?string $heading = "Kandidatlar link bo'yicha";

    /**
     * Chart options (series, labels, types, size, animations...)
     * https://apexcharts.com/docs/options
     *
     * @return array
     */
    protected function getOptions(): array
    {
        // Group and count by source (LEFT JOIN to include NULLs)
        $rows = DB::table('applications as a')
            ->leftJoin('sources as s', 's.id', '=', 'a.source_id')
            ->selectRaw("COALESCE(s.name, 'default') as source_name, COUNT(*) as total")
            ->groupBy('source_name')
            ->orderByDesc('total')
            ->get();

        $categories = $rows->pluck('source_name')->toArray();
        $seriesData = $rows->pluck('total')->map(fn ($v) => (int) $v)->toArray();

        return [
            'chart' => [
                'type' => 'bar',
                'height' => 380,
            ],
            'series' => [
                [
                    'name' => 'Applications',
                    'data' => $seriesData,
                ],
            ],
            'xaxis' => [
                'categories' => $categories,
                'labels' => [
                    'rotate' => -20,
                    'style' => [
                        'fontFamily' => 'inherit',
                        'fontSize' => '12px',
                    ],
                    // If you expect long names, you can truncate:
                    // 'formatter' => new \Illuminate\Support\Js("function (val) { return (val.length>14) ? val.slice(0,12)+'…' : val }"),
                ],
            ],
            'yaxis' => [
                'title' => ['text' => 'Kandidatlar soni'],
                'labels' => [
                    'style' => ['fontFamily' => 'inherit'],
                ],
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
            'grid' => ['strokeDashArray' => 3],
            'tooltip' => ['enabled' => true],
            'colors' => ['#10b981'], // teal — change if you like
        ];
    }
}
