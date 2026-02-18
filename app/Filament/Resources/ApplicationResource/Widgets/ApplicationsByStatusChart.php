<?php

namespace App\Filament\Resources\ApplicationResource\Widgets;

use Illuminate\Support\Facades\DB;
use Leandrocfe\FilamentApexCharts\Widgets\ApexChartWidget;

class ApplicationsByStatusChart extends ApexChartWidget
{
    /**
     * Chart Id
     *
     * @var string
     */
    protected static ?string $chartId = 'applicationsByStatusChart';

    /**
     * Widget Title
     *
     * @var string|null
     */
    protected static ?string $heading = "Kandidatlar statusi bo'yicha";

    /**
     * Chart options (series, labels, types, size, animations...)
     * https://apexcharts.com/docs/options
     *
     * @return array
     */
    protected function getOptions(): array
    {
        // Count apps per status (no translation join here to avoid duplication)
        $rows = DB::table('applications as a')
            ->join('statuses as s', 's.id', '=', 'a.status_id')
            ->select([
                's.id',
                DB::raw("(SELECT st2.name
                      FROM status_translations st2
                      WHERE st2.status_id = s.id
                      ORDER BY st2.id ASC
                      LIMIT 1) AS status_name"),
                DB::raw('COUNT(*)::int AS total'), // Postgres; for MySQL use COUNT(*) AS total
            ])
            ->groupBy('s.id')
            ->orderByDesc('total')
            ->get();

        // Build categories with a safe fallback label
        $categories = $rows->map(fn ($r) => $r->status_name ?? ('Status #'.$r->id))->toArray();
        $seriesData = $rows->pluck('total')->toArray();

        return [
            'chart' => ['type' => 'bar', 'height' => 380],
            'series' => [[ 'name' => 'Kandidatlar', 'data' => $seriesData ]],
            'xaxis' => [
                'categories' => $categories,
                'labels' => [ 'rotate' => -20, 'style' => ['fontFamily' => 'inherit', 'fontSize' => '12px'] ],
            ],
            'yaxis' => [ 'title' => ['text' => 'Arizalar soni'] ],
            'plotOptions' => [ 'bar' => [ 'horizontal' => false, 'columnWidth' => '50%', 'borderRadius' => 4 ] ],
            'dataLabels' => ['enabled' => false],
            'stroke' => ['show' => true, 'width' => 2],
            'tooltip' => ['enabled' => true],
            'grid' => ['strokeDashArray' => 3],
            'colors' => ['#a855f7'],
        ];
    }


}
