<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CourseResource\RelationManagers\PaymentsRelationManager;
use App\Filament\Resources\ProgramResource\Pages;
use App\Filament\Resources\ProgramResource\RelationManagers;
use App\Filament\Resources\ProgramResource\RelationManagers\ParticipantRelationManager;
use App\Filament\Resources\ProgramResource\RelationManagers\PaysRelationManager;
use App\Filament\Resources\ProgramResource\RelationManagers\ScheduleRelationManager;
use App\Filament\Resources\ProgramResource\Widgets\ProgramOverview;
use App\Models\Course;
use App\Models\Program;
use Filament\Forms;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Group;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\SpatieMediaLibraryFileUpload;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\ToggleButtons;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationGroup;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Columns\Summarizers\Summarizer;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Enums\FiltersLayout;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Tables\Columns\Summarizers\Sum;
use Livewire;

class ProgramResource extends Resource
{
    protected static ?string $model = Course::class;

    protected static ?string $navigationLabel = 'Programs';
    protected static ?string $label = 'Programs';

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function getEloquentQuery(): Builder
    {
        return static::getModel()::query()->orderBy('id', 'desc');
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Course')
                    ->columns(2)
                    ->description('Enter the Course Details')
                    ->schema([
                        Group::make()
                            ->columns(3)
                            ->schema([
                                ToggleButtons::make('type')
                                    ->options([
                                        'public' => 'Public',
                                        'corporate' => 'Corporate',
                                    ])
                                    ->colors([
                                        'public' => 'info',
                                        'corporate' => 'success',
                                    ])
                                    ->required()
                                    ->reactive()
                                    ->inline()
                                    ->afterStateUpdated(function (callable $set, $state, $get, ?Course $record) {
                                        // Example: Update a field in the ownerRecord
                                        if ($record) {
                                            $record->update([
                                                'type' => $state, // Update the type based on toggle value
                                            ]);
                                            // $this->emit('refreshRelationManager');
                                            redirect(request()->header('Referer'));

                                        }
                                    }),
                                ToggleButtons::make('locations')
                                    ->options([
                                        0 => 'other site',
                                        1 => 'on site',
                                    ])

                                    ->required()
                                    ->inline(),
                                ToggleButtons::make('	course_type')
                                    ->options([
                                        'face_to_face' => 'F2F',
                                        'online' => 'Online',
                                    ])

                                    ->inline(),
                            ]),


                        TextInput::make('name')->required(),
                        Group::make()
                            ->columns(2)

                            ->schema([
                                TextInput::make('duration')
                                    ->numeric()
                                    ->required(),
                                Select::make('duration_type')
                                    ->options([
                                        "hours" => "Hours",
                                        "days" => "Days",
                                        "weeks" => "Weeks",
                                        "months" => "Months",
                                        "years" => "Years",
                                    ]),
                            ]),
                        Select::make('topics')
                            ->multiple()
                            ->searchable()
                            ->preload()
                            ->relationship('Topics', 'name'),
                        Select::make('lead_id')
                            ->label('Lead Account Manager')
                            ->searchable()
                            ->preload()
                            ->relationship('leader', 'name'),




                        Group::make()
                            ->columns(2)
                            ->schema([
                                DatePicker::make('start_date')->native(false),
                                DatePicker::make('end_date')->native(false),
                            ]),
                        RichEditor::make('details')->label('Notes')->columnSpan(2)
                    ]),
                Section::make('Client')

                    ->visible(fn($get) => $get('type') == 'corporate')
                    ->reactive()
                    ->columns(3)
                    ->description('Enter the Client Details')

                    ->schema([
                        Select::make('company')
                            ->label('Client')
                            ->searchable()
                            ->afterStateUpdated(function (callable $set, $state) {
                                $set('pays', [
                                    ['entity_id' => $state], // Correct format
                                ]);
                            })
                            ->createOptionForm([
                                TextInput::make('name')
                                    ->required()
                                    ->maxLength(255),
                                TextInput::make('email')
                                    ->email()
                                    ->required()
                                    ->maxLength(255),
                                TextInput::make('phone')
                                    ->tel()
                                    ->maxLength(15),
                                SpatieMediaLibraryFileUpload::make('image')
                                    ->collection('companies'),

                                Repeater::make('liaison_officer')
                                    ->label('Liaison Officer')
                                    ->schema([
                                        TextInput::make('title')
                                            ->label('Title')
                                            ->required()
                                            ->maxLength(255),
                                        TextInput::make('name')
                                            ->label('Name')
                                            ->required()
                                            ->maxLength(255),
                                        TextInput::make('phone')
                                            ->label('Phone')
                                            ->tel()
                                            ->maxLength(15),
                                        TextInput::make('email')
                                            ->label('Email')
                                            ->email()
                                            ->required()
                                            ->maxLength(255),
                                        TextInput::make('note')
                                            ->label('Note')
                                            ->maxLength(65535),
                                    ])
                                    ->columns(5)
                                    ->columnSpan(2),
                                RichEditor::make('note')
                                    ->label('Note')
                                    ->columnSpan(2)
                                    ->maxLength(65535),
                                Toggle::make('active')
                                    ->label('Active')
                                    ->default(true),

                            ])
                            ->reactive()
                            ->preload()
                            ->relationship('company', 'name'),
                        TextInput::make('price')->label('Fees')->numeric(),
                        Toggle::make('invoiced')
                            ->inline(false),

                        Repeater::make('pays')
                            ->label('Payments')
                            ->columnSpan(3)
                            ->defaultItems(0)
                            ->relationship('pays')
                            ->schema([
                                DatePicker::make('date')->required(),
                                TextInput::make('amount')->required(),
                                TextInput::make('invoice_id')->required(),
                                Hidden::make('entity_id')->reactive()->default(fn() => self::getRecord() ?? null),
                                Hidden::make('entity_type')->default('company'),
                            ])
                            ->columns(3)
                            ->reactive(),
                    ])
            ]);
    }

    public static function getRecord()
    {
        if (request()->toArray() == null) {
            return;
        }
        $ar = json_decode(request()->toArray()['components'][0]['snapshot'], true);
        $company_id = $ar['data']['data'][0]['company_id'];
        return $company_id;

    }



    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')->searchable(),
                TextColumn::make('company_name')->label('Client'),
                TextColumn::make('total_price')->label('Price')
                    ->summarize(
                        Summarizer::make()->money('JOD')
                            ->label('Total')
                            ->using(function ($query) {
                                $foreach = $query->get();
                                $total = 0;
                                // dd($foreach);
                                foreach ($foreach as $item) {

                                    $total += Course::find($item->id)->total_price;
                                }
                                return $total;
                            })

                    ),
                TextColumn::make('payments')
                    ->getStateUsing(function ($record) {
                        return $record->sumTraineePayments();
                    }),

                TextColumn::make('type')
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'corporate' => 'warning',
                        'public' => 'success',
                    }),

                TextColumn::make('invoiced')
                    ->sortable()
                    ->badge()
                ,



                TextColumn::make('location')
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'other site' => 'primary',
                        'on site' => 'gray',
                    }),
                TextColumn::make('topics.name'),
                TextColumn::make('duration')
                    ->getStateUsing(function ($record) {
                        return $record->duration . ' ' . $record->duration_type;
                    }),
                TextColumn::make('user_count')
                    ->getStateUsing(function ($record) {
                        return $record->userCount();
                    })
                    ->badge(),



            ])
            ->filters([
                SelectFilter::make('topics')
                    ->multiple(true)
                    ->searchable()
                    ->preload()
                    ->relationship('topics', 'name'),
                SelectFilter::make('company')
                ->label('Client')
                    // ->multiple(true)
                    ->searchable()
                    ->preload()
                    ->relationship('company', 'name'),
                SelectFilter::make('type')
                    ->options([
                        0 => 'Public',
                        1 => 'Corporate',
                    ])
                    ->attribute('type')

            ], FiltersLayout::AboveContent)
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            RelationGroup::make('Contacts', [
                ScheduleRelationManager::class,
                    // PaysRelationManager::class,
                ParticipantRelationManager::class,
                // PaymentsRelationManager::class
            ])
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPrograms::route('/'),
            'create' => Pages\CreateProgram::route('/create'),
            'edit' => Pages\EditProgram::route('/{record}/edit'),
        ];
    }


    public static function getWidgets(): array
    {
        return [
            // ProgramOverview::class,

        ];
    }
}
