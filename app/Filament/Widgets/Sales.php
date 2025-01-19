<?php

namespace App\Filament\Widgets;

use App\Models\Course;
use App\Models\Supervisor;
use Carbon\Carbon;
use Filament\Widgets\ChartWidget;
use Flowframe\Trend\Trend;
use Flowframe\Trend\TrendValue;

class Sales extends ChartWidget
{
    protected static ?string $heading = 'Monthly Sales (Clients)';

    protected static ?string $maxHeight = "250px";

    protected function getData(): array
    {
        $chartDataC = [];
        $chartDataP = [];
        $chartDataU = [];

        for ($i = 0; $i < 12; $i++) { // Adjusted the loop to start from 0 for better clarity
            // Calculate the start and end of each month relative to the current date
            $startOfMonth = Carbon::now()->subMonthsNoOverflow($i)->startOfMonth();
            $endOfMonth = Carbon::now()->subMonthsNoOverflow($i)->endOfMonth();

            // Fetch and calculate total revenue for the month
            $monthlyRevenue = Course::whereBetween('created_at', [$startOfMonth, $endOfMonth])->get()->sum('total_price');

            // Add the revenue to the chart data
            $chartDataC[] = $monthlyRevenue;

            $startOfMonth = Carbon::now()->subMonthsNoOverflow($i)->startOfMonth();
            $endOfMonth = Carbon::now()->subMonthsNoOverflow($i)->endOfMonth();

            // Fetch and calculate total revenue for the month
            $monthlyRevenueP = Course::whereBetween('created_at', [$startOfMonth, $endOfMonth])->get()->sum(function ($course) {
                return $course->sumTraineePayments();
            });

            // Add the revenue to the chart data
            $chartDataP[] = $monthlyRevenueP;
            $chartDataU[] = $monthlyRevenue - $monthlyRevenueP;
        }

        // Reverse the data to align with the labels (last 12 months in ascending order)
        $chartDataC = array_reverse($chartDataC);

        // Generate month labels dynamically
        $labels = [];
        for ($i = 0; $i < 12; $i++) {
            $labels[] = Carbon::now()->subMonthsNoOverflow($i)->shortMonthName;
        }
        $labels = array_reverse($labels); // Reverse to match data order

        $data = [
            'label' => 'Total Sales',
            'data' => $chartDataC,
            'backgroundColor' => '#36A2EB',
            'borderColor' => '#9BD0F5',
        ];




       

        // Reverse the data to align with the labels (last 12 months in ascending order)
        $chartDataP = array_reverse($chartDataP);
        $chartDataU = array_reverse($chartDataU);

        // Generate month labels dynamically

        $data2 = [
            'label' => 'Total Paid',
            'data' => $chartDataP,
            'backgroundColor' => '#4caf50',
            'borderColor' => '#4caf50',
        ];
        $data3 = [
            'label' => 'Total Unpaid',
            'data' => $chartDataU,
            'backgroundColor' => '#E91E63',
            'borderColor' => '#E91E63',
        ];




        

        return [
            'datasets' => [
                $data,
                $data2,
                $data3,
            ],
            'labels' => $labels,
        ];
    }

    protected function getType(): string
    {
        return 'bar';
    }
}
