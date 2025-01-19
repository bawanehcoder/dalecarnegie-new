<?php

namespace App\Filament\Widgets;

use App\Models\Course;
use Filament\Widgets\ChartWidget;

class Type extends ChartWidget
{
    protected static ?string $heading = 'Type';

    protected static ?string $maxHeight = "250px";


    protected function getData(): array
    {
        $publiccourses = Course::where('type', 0)->get()->count();
        $corporatecourses = Course::where('type', 1)->get()->count();
        return [
            "labels" => [
                'Public',
                'Corporate',
            ],
            "datasets" => [
                [
                    "data" => [$publiccourses,$corporatecourses],
                    "backgroundColor" => [
                        'rgb(255, 205, 86)',
                        'rgb(255, 99, 132)',
                    ],
                    "hoverOffset" => 4
                ]
            ]
        ];
    }

    protected function getType(): string
    {
        return 'doughnut';
    }
}
