<?php

namespace App\Filament\Widgets;

use App\Models\Company;
use App\Models\Course;
use App\Models\User;
use Carbon\Carbon;
use Filament\Widgets\Concerns\InteractsWithPageFilters;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class ProgramOverview extends BaseWidget
{
    use InteractsWithPageFilters;
    protected function getColumns(): int
    {
        $count = count($this->getCachedStats());

        if ($count < 3) {
            return 3;
        }

        if (($count % 3) !== 1) {
            return 3;
        }

        return 3;
    }
    protected function getStats(): array
    {
        $year = $this->filters['year'] ?? Carbon::now()->startOfYear();


        $lastMonthRevenue = Course::whereBetween('start_date', [
            Carbon::parse("{$year}-01-01")->subYear()->startOfYear(),
            Carbon::parse("{$year}-01-01")->subYear()->endOfYear(),
        ])->get()->sum(function ($course) {
            return $course->totalPrice();
        });

        $thisMonthRevenue = Course::whereBetween('start_date', [
            Carbon::parse("{$year}-01-01")->startOfYear(),
            Carbon::parse("{$year}-01-01")->endOfYear(),
        ])->get()->sum(function ($course) {
            return $course->totalPrice();
        });

        $revenueIncrease = $thisMonthRevenue - $lastMonthRevenue;
        $percentageIncrease = $lastMonthRevenue > 0 ? ($revenueIncrease / $lastMonthRevenue) * 100 : $revenueIncrease;



        // $total = Course::sum('price');
        $total = Course::get()->sum(function ($course) {
            return $course->totalPrice();
        });

        $totalPaid = Course::all()->sum(function ($course) {
            return $course->sumTraineePayments();
        });
        $totalUnPaid = $total - $totalPaid;

        $chartData = [];

        for ($i = 0; $i < 12; $i++) {
            $startOfMonth = Carbon::parse("{$year}-{$i}-01")->startOfMonth();
            $endOfMonth = Carbon::parse("{$year}-{$i}-01")->endOfMonth();

            // Fetch and calculate total revenue for the month
            $weeklyRevenue = Course::whereBetween('start_date', [$startOfMonth, $endOfMonth])->get()->sum('total_price');
            $chartData[] = $weeklyRevenue;
        }

        $courses = Course::all()->count();
        $publiccourses = Course::where('type', 0)->get()->count();
        $corporatecourses = Course::where('type', 1)->get()->count();

        $users = User::where('type', 0)->get()->count();
        $publicusers = Course::where('type', 0)->get()->sum(function ($course) {
            return $course->userCount();
        });
        $corporateusers = Course::where('type', 1)->get()->sum(function ($course) {
            return $course->userCount();
        });

        $trainers = User::where('type', 3)->get()->count();

        $trainersPrice = Course::all()->sum(function ($course) {
            return $course->getSchedule()->sum('fees');
        });


        $lastMonthRevenueC = Course::where('type', 1)->whereBetween('created_at', [
            Carbon::now()->subYear()->startOfYear(),
            Carbon::now()->subYear()->endOfYear(),
        ])->sum('total_price');

        $thisMonthRevenueC = Course::where('type', 1)->whereBetween('created_at', [
            Carbon::now()->startOfYear(),
            Carbon::now()->endOfYear(),
        ])->sum('total_price');

        $revenueIncreaseC = $thisMonthRevenueC - $lastMonthRevenueC;
        $percentageIncreaseC = $lastMonthRevenueC > 0 ? ($revenueIncreaseC / $lastMonthRevenueC) * 100 : $revenueIncreaseC;



        $totalC = Course::where('type', 1)->get()->sum('total_price');
        $totalPaidC = Course::where('type', 1)->get()->sum(function ($course) {
            return $course->sumTraineePayments();
        });
        $totalUnPaidC = $totalC - $totalPaidC;

        $chartDataC = [];

        for ($i = 0; $i < 12; $i++) {
            $startOfMonth = Carbon::now()->subMonthsNoOverflow($i)->startOfMonth();
            $endOfMonth = Carbon::now()->subMonthsNoOverflow($i)->endOfMonth();

            // Fetch and calculate total revenue for the month
            $weeklyRevenueC = Course::where('type', 1)->whereBetween('created_at', [$startOfMonth, $endOfMonth])->get()->sum('total_price');
            $chartDataC[] = $weeklyRevenueC;
        }







        $lastMonthRevenueP = Course::where('type', 0)->whereBetween('created_at', [
            Carbon::now()->subYear()->startOfYear(),
            Carbon::now()->subYear()->endOfYear(),
        ])->sum('total_price');

        $thisMonthRevenueP = Course::where('type', 0)->whereBetween('created_at', [
            Carbon::now()->startOfYear(),
            Carbon::now()->endOfYear(),
        ])->sum('total_price');

        $revenueIncreaseP = $thisMonthRevenueP - $lastMonthRevenueP;
        $percentageIncreaseP = $lastMonthRevenueP > 0 ? ($revenueIncreaseP / $lastMonthRevenueP) * 100 : $revenueIncreaseP;



        $totalP = Course::where('type', 0)->get()->sum('total_price');
        $totalPaidP = Course::where('type', 0)->get()->sum(function ($course) {
            return $course->sumTraineePayments();
        });
        $totalUnPaidP = $totalP - $totalPaidP;

        $chartDataP = [];

        for ($i = 0; $i < 12; $i++) {
            $startOfMonth = Carbon::now()->subMonthsNoOverflow($i)->startOfMonth();
            $endOfMonth = Carbon::now()->subMonthsNoOverflow($i)->endOfMonth();

            // Fetch and calculate total revenue for the month
            $weeklyRevenueP = Course::where('type', 0)->whereBetween('created_at', [$startOfMonth, $endOfMonth])->get()->sum('total_price');
            $chartDataP[] = $weeklyRevenueP;
        }






        return [
            Stat::make('Total Courses Price', 'JOD ' . number_format($total, 2))
                ->description(
                    'Year Change : ' .
                    $revenueIncrease == 0 ? '0' :
                    ' (' . number_format($percentageIncrease, 2) . '%)'
                )
                ->chart($chartData)
                ->color($revenueIncrease > 0 ? 'success' : ($revenueIncrease < 0 ? 'danger' : 'primary')),


            Stat::make('Total Paid ( Courses )', 'JOD ' . number_format($totalPaid, 2))
                ->color('success'),

            Stat::make('Total Unpaid ( Courses )', 'JOD ' . number_format($totalUnPaid, 2))
                ->color('success'),



            Stat::make('All Courses', $courses)
                ->color('success'),
            Stat::make('Public Courses', $publiccourses)
                ->color('success'),
            Stat::make('Corporate Courses', $corporatecourses)
                ->color('success'),


            Stat::make('All Participants', $users)
                ->color('success'),
            Stat::make('Public Participants', $publicusers)
                ->color('success'),
            Stat::make('Corporate Participants', $corporateusers)
                ->color('success'),

            Stat::make('All Trainers', $trainers)
                ->color('success'),

            Stat::make('All Trainers Price', 'JOD ' . $trainersPrice)
                ->color('success'),
            Stat::make('All Trainers Price ( Paid )', 'JOD ' . $trainersPrice)
                ->color('success'),


            Stat::make('Total Courses Price ( Corporate )', 'JOD ' . number_format($totalC, 2))
                ->description(
                    'Year Change : ' . ($revenueIncreaseC >= 0 ? '+' : '-') . round($totalC / 1000, 1) . 'k' .
                    ' (' . number_format($percentageIncreaseC, 2) . '%)'
                )
                ->chart($chartDataC)
                ->color('success'),

            Stat::make('Total Paid ( Corporate )', 'JOD ' . number_format($totalPaidC, 2))
                ->color('success'),

            Stat::make('Total Unpaid ( Corporate )', 'JOD ' . number_format($totalUnPaidC, 2))
                ->color('success'),



            Stat::make('Total Courses Price ( Public )', 'JOD ' . number_format($totalP, 2))
                ->description(
                    'Year Change : ' . ($revenueIncreaseP >= 0 ? '+' : '-') . round($totalP / 1000, 1) . 'k' .
                    ' (' . number_format($percentageIncreaseP, 2) . '%)'
                )
                ->chart($chartDataP)
                ->color('success'),

            Stat::make('Total Paid ( Public )', 'JOD ' . number_format($totalPaidP, 2))
                ->color('success'),

            Stat::make('Total Unpaid ( Public )', 'JOD ' . number_format($totalUnPaidP, 2))
                ->color('success'),


            Stat::make('Total Clients', Company::all()->count())
                // ->columnSpan(3)
                ->color('success'),
        ];
    }
}
