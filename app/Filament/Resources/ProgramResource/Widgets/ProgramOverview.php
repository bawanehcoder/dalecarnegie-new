<?php

namespace App\Filament\Resources\ProgramResource\Widgets;

use App\Filament\Resources\ProgramResource\Pages\ListPrograms;
use App\Models\Course;
use Carbon\Carbon;
use Filament\Widgets\Concerns\InteractsWithPageTable;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class ProgramOverview extends BaseWidget
{
    use InteractsWithPageTable;

    protected function getTablePage(): string
    {
        return ListPrograms::class;
    }
    protected function getStats(): array
    {

        $lastMonthRevenue = Course::whereBetween('created_at', [
            Carbon::now()->subYear()->startOfYear(),
            Carbon::now()->subYear()->endOfYear(),
        ])->sum('total_price');
        
        $thisMonthRevenue = Course::whereBetween('created_at', [
            Carbon::now()->startOfYear(),
            Carbon::now()->endOfYear(),
        ])->sum('total_price');
        
        $revenueIncrease = $thisMonthRevenue - $lastMonthRevenue;
        $percentageIncrease = $lastMonthRevenue > 0 ? ($revenueIncrease / $lastMonthRevenue) * 100 : 0;

        

        $total = $this->getPageTableQuery()->get()->sum(function ($course) {
            return $course->totalPrice();
        });
        $totalPaid = $this->getPageTableQuery()->get()->sum(function ($course) {
            return $course->sumTraineePayments();
        });
        $totalUnPaid = $total - $totalPaid;

        $chartData = [];

        for ($i = 1; $i <= 12; $i++) {
            $startOfWeek = Carbon::now()->subMonth($i)->startOfMonth();
            $endOfWeek = Carbon::now()->subMonth($i)->endOfMonth();

            $weeklyRevenue = Course::whereBetween('created_at', [$startOfWeek, $endOfWeek])->sum('price');
            $chartData[] = $weeklyRevenue;
        }



        return [
            Stat::make('Total Price', 'JOD ' . number_format($total, 2))
            ->description(
                'Year Change : ' .($revenueIncrease >= 0 ? '+' : '-') . round($total / 1000, 1) . 'k' . 
                ' (' . number_format($percentageIncrease, 2) . '%)'
            )
            ->chart($chartData)
                ->color('success'),

            Stat::make('Total Paid', 'JOD ' . number_format($totalPaid, 2))
                ->color('success'),

            Stat::make('Total Unpaid', 'JOD ' . number_format($totalUnPaid, 2))
                ->color('success'),
        ];
    }
}
