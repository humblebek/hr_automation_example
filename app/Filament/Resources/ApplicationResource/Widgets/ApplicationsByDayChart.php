<?php

namespace App\Filament\Resources\ApplicationResource\Widgets;

use BezhanSalleh\FilamentShield\Traits\HasWidgetShield;
use Leandrocfe\FilamentApexCharts\Widgets\ApexChartWidget;
use App\Models\Application;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;

class ApplicationsByDayChart extends ApexChartWidget
{

    use HasWidgetShield;

    protected int | string | array $columnSpan = 'full';
    /**
     * Chart Id
     *
     * @var string
     */
    protected static ?string $chartId = 'applicationsByDayChart';

    /**
     * Widget Title
     *
     * @var string|null
     */
    protected static ?string $heading = 'Kandidatlar kunlik';

    /**
     * Chart options (series, labels, types, size, animations...)
     * https://apexcharts.com/docs/options
     *
     * @return array
     */


    protected function getOptions(): array
    {
        // Show last 30 days of applications
        $startDate = Carbon::now()->subDays(30)->startOfDay();

        // Group by day and count
        $statistics = Application::query()
            ->where('created_at', '>=', $startDate)
            ->select(
                DB::raw('DATE(created_at) as date'),
                DB::raw('COUNT(*) as count')
            )
            ->groupBy(DB::raw('DATE(created_at)'))
            ->orderBy(DB::raw('DATE(created_at)'))
            ->get()
            ->pluck('count', 'date')
            ->toArray();

        // Fill missing days with zero
        $period = new \DatePeriod($startDate, new \DateInterval('P1D'), Carbon::now()->addDay());
        $dates = [];
        $counts = [];

        foreach ($period as $date) {
            $d = $date->format('Y-m-d');
            $dates[] = $d;
            $counts[] = $statistics[$d] ?? 0;
        }

        return [
            'chart' => [
                'type' => 'line',
                'height' => 300,
            ],
            'series' => [
                [
                    'name' => 'Kunlik kandidatlar',
                    'data' => $counts,
                ],
            ],
            'xaxis' => [
                'categories' => $dates,
                'labels' => [
                    'rotate' => -45,
                    'style' => [
                        'fontFamily' => 'inherit',
                        'fontSize' => '10px',
                    ],
                ],
            ],
            'yaxis' => [
                'title' => [
                    'text' => 'Kandidatlar soni',
                ],
                'labels' => [
                    'style' => [
                        'fontFamily' => 'inherit',
                    ],
                ],
            ],
            'colors' => ['#2563eb'], // blue tone
            'stroke' => [
                'curve' => 'smooth',
                'width' => 3,
            ],
            'markers' => [
                'size' => 5,
            ],
            'grid' => [
                'borderColor' => '#f1f1f1',
            ],
            'tooltip' => [
                'enabled' => true,
                'x' => [
                    'format' => 'yyyy-MM-dd',
                ],
            ],
        ];
    }

}
