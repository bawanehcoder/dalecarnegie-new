<?php

namespace App\Filament\Reports;

use App\Models\Course;
use EightyNine\Reports\Components\Body\TextColumn;
use EightyNine\Reports\Components\Text;
use EightyNine\Reports\Components\VerticalSpace;
use EightyNine\Reports\Report;
use EightyNine\Reports\Components\Body;
use EightyNine\Reports\Components\Footer;
use EightyNine\Reports\Components\Header;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Form;

class CoursesReport extends Report
{
    public ?string $heading = "Report";

    // public ?string $subHeading = "A great report";

    public function header(Header $header): Header
    {
        return $header
            ->schema([
                Header\Layout\HeaderRow::make()
                ->schema([
                    Header\Layout\HeaderColumn::make()
                        ->schema([
                            Text::make("Financial report")
                                ->title()
                                ->primary(),
                            
                        ]),
                    Header\Layout\HeaderColumn::make()
                        ->schema([
                            Text::make("DaleCarnegie")
                                ->title()
                                ->primary(),
                        ])
                        ->alignRight(),
                ]),
            ]);
    }


    public function body(Body $body): Body
    {
        return $body
            ->schema([
                Body\Layout\BodyColumn::make()
                    ->schema([
                        Body\Table::make()
                            ->columns([
                                TextColumn::make("company_name"),
                                TextColumn::make("name"),
                                TextColumn::make("total_price")->sum()->money('JOD'),
                                TextColumn::make("type"),
                                TextColumn::make("leader_name"),
                            ])
                            ->data(
                                fn(?array $filters) => $filters ? Course::whereBetween('start_date', [$filters['start'], $filters['end']])->get() : collect()
                            ),
                        // VerticalSpace::make(),
                        // Body\Table::make()
                        //     ->data(
                        //         fn(?array $filters) => $this->verificationSummary($filters)
                        //     ),
                    ]),
            ]);
    }

    public function footer(Footer $footer): Footer
    {
        return $footer
        ->schema([
            Footer\Layout\FooterRow::make()
                ->schema([
                    Footer\Layout\FooterColumn::make()
                        ->schema([
                            Text::make("DaleCarnegie")
                                ->title()
                                ->primary(),
                            Text::make("Financial report")
                                ->subtitle(),
                        ]),
                    Footer\Layout\FooterColumn::make()
                        ->schema([
                            Text::make("Generated on: " . now()->format('Y-m-d H:i:s')),
                        ])
                        ->alignRight(),
                ]),
        ]);
    }

    public function filterForm(Form $form): Form
    {
        return $form
            ->schema([
                DatePicker::make('start'),
                DatePicker::make('end'),
            ]);
    }
}
