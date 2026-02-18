<?php

namespace App\Filament\Resources\ApplicationResource\Widgets;

use App\Enum\Natinalities;
use App\Models\Application;
use Illuminate\Support\Facades\DB;
use Leandrocfe\FilamentApexCharts\Widgets\ApexChartWidget;

class ApplicationsByNationalityChart extends ApexChartWidget
{
    /**
     * Chart Id
     *
     * @var string
     */
    protected static ?string $chartId = 'applicationsByNationalityChart';

    /**
     * Widget Title
     *
     * @var string|null
     */
    protected static ?string $heading = "Millati bo'yicha kandidatlar";

    /**
     * Chart options (series, labels, types, size, animations...)
     * https://apexcharts.com/docs/options
     *
     * @return array
     */
    protected function getOptions(): array
    {
        // DB counts grouped by enum value
        $rows = Application::query()
            ->select('nationality', DB::raw('COUNT(*) as total'))
            ->groupBy('nationality')
            ->get()
            ->pluck('total', 'nationality')
            ->toArray();

        // Build categories in enum order (so missing ones appear as 0)
        $categories = [];
        $seriesData = [];
        foreach (Natinalities::cases() as $n) {
            $categories[] = $n->label();
            $seriesData[] = (int) ($rows[$n->value] ?? 0);
        }

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
                    'rotate' => -20,
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
            'colors' => ['#22c55e'],
        ];
    }
}
