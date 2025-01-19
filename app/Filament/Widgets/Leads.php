<?php

namespace App\Filament\Widgets;

use App\Models\Course;
use App\Models\Supervisor;
use App\Models\User;
use Carbon\Carbon;
use Filament\Widgets\ChartWidget;
use Flowframe\Trend\Trend;
use Flowframe\Trend\TrendValue;

class Leads extends ChartWidget
{
    protected static ?string $heading = 'Lead Account Managers Sales';

    protected static ?int $sort = 20;
    protected int | string | array $columnSpan = 2;

    protected static ?string $maxHeight = "300px";



    protected function getData(): array
    {

        $s1 = Supervisor::find(1);

        $trend1 = Trend::query(Course::where('lead_id', '=', $s1->id))
            ->between(
                start: now()->startOfYear(),
                end: now()->endOfYear(),
            )
            ->perMonth()
            ->sum('price');

            $data=  [
                'label' => $s1->name,
                'data' =>  $trend1->map(fn (TrendValue $value) => $value->aggregate) ,
                'backgroundColor' => '#36A2EB',
                'borderColor' => '#9BD0F5',
            ];


            $s2 = Supervisor::find(2);

        $trend2 = Trend::query(Course::where('lead_id', '=', $s2->id))
            ->between(
                start: now()->startOfYear(),
                end: now()->endOfYear(),
            )
            ->perMonth()
            ->sum('price');

            $data2=  [
                'label' => $s2->name,
                'data' =>  $trend2->map(fn (TrendValue $value) => $value->aggregate) ,
                'backgroundColor' => '#4caf50',
                'borderColor' => '#4caf50',
            ];


            $s3 = Supervisor::find(3);

        $trend3 = Trend::query(Course::where('lead_id', '=', $s3->id))
            ->between(
                start: now()->startOfYear(),
                end: now()->endOfYear(),
            )
            ->perMonth()
            ->sum('price');

            $data3=  [
                'label' => $s3->name,
                'data' =>  $trend3->map(fn (TrendValue $value) => $value->aggregate) ,
                'backgroundColor' => '#E91E63',
                'borderColor' => '#E91E63',
            ];
            

        // dd($data);#E91E63


        return [
            'datasets' => [
               $data,
               $data2,
               $data3,
            ],
            'labels' => ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'],
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }
}
