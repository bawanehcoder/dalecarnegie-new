<?php

namespace App\Filament\Resources\ProgramResource\RelationManagers;

use App\Models\User;
use Filament\Actions\Action;
use Filament\Forms;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\TimePicker;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Livewire;
use Schedule;
class ScheduleRelationManager extends RelationManager
{
    protected static string $relationship = 'schedule';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Select::make('trainer_id')
                    ->relationship('trainer', 'name')
                    ->searchable()
                    ->preload()
                    ->required()
                    ->reactive()
                    ->afterStateUpdated(function ($state, callable $set) {
                        // dd($state);
                        // جلب رسوم المدرب
                        $trainer = User::find($state);
                        // dd($trainer);
                        $set('fees', $trainer->fees ?? 0);

                    }),

                DatePicker::make('date')
                    ->native(false)
                    ->required(),

                TimePicker::make('from_time')
                    ->required()
                    ->reactive(), // التحديث التلقائي عند التغيير

                TimePicker::make('end_time')
                    ->required()
                    ->reactive()
                    ->afterStateUpdated(function ($state, callable $get, callable $set) {
                        $fromTime = $get('from_time');
                        $endTime = $get('end_time');

                        if ($fromTime && $endTime) {
                            // حساب الفرق بالساعات
                            $fromTime = \Carbon\Carbon::parse($fromTime);
                            $endTime = \Carbon\Carbon::parse($endTime);

                            $differenceInHours = $fromTime->diffInMinutes($endTime) / 60;


                            // dd($differenceInHours);
            
                            // تحديث قيمة fees بناءً على الفرق بالساعات
                            $fees = User::find($get('trainer_id'))->fees;
                            if ($differenceInHours <= 4) {
                                $set('fees', $fees / 2); // نصف القيمة
                            } else {
                                $set('fees', $fees); // القيمة الكاملة
                            }
                        }
                    }),

                Select::make('payment_method')
                    ->options([
                        'cash' => 'Cash',
                        'credit' => 'Credit',
                        'cheque' => 'Cheque',
                    ]),

                TextInput::make('fees')
                    ->required(), // تعطيل الإدخال اليدوي

            ]);
    }





    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('id')
            ->columns([
                Tables\Columns\TextColumn::make('date'),
                Tables\Columns\TextColumn::make('from_time'),
                Tables\Columns\TextColumn::make('end_time'),
                Tables\Columns\TextColumn::make('trainer.name'),
                Tables\Columns\TextColumn::make('payment_method'),
                Tables\Columns\TextColumn::make('fees'),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make(),
                \Filament\Tables\Actions\Action::make('accept')
                    ->label('Add ( All Days )')
                    ->button()
                    ->color('success')
                    // ->icon('heroicon-o-check')
                    ->modalHeading('Select Option')
                    ->action(function ($record, array $data) {
                        // dd($data['OptID']);

                        $record = $this->ownerRecord; // الحصول على السجل المحفوظ

                        $days = (int)$record->duration; // Number of days to loop
                        $baseDate = \Carbon\Carbon::parse($data['date']); // Base date for incrementing
                
                        // Loop to create records for each day
                        for ($i = 0; $i < $days; $i++) {
                            $newDate = $baseDate->copy()->addDays($i);
                            \App\Models\Schedule::create([
                                'trainer_id' => $data['trainer_id'],
                                'course_id' => $record->id,
                                'date' => $newDate->toDateString(),
                                'from_time' => $data['from_time'],
                                'end_time' => $data['end_time'],
                                'payment_method' => $data['payment_method'],
                                'fees' => $data['fees'],
                            ]);
                        }
        
                    })
                    ->form([
                        Select::make('trainer_id')
                    ->relationship('trainer', 'name')
                    ->searchable()
                    ->preload()
                    ->required()
                    ->reactive()
                    ->afterStateUpdated(function ($state, callable $set) {
                        // dd($state);
                        // جلب رسوم المدرب
                        $trainer = User::find($state);
                        // dd($trainer);
                        $set('fees', $trainer->fees ?? 0);

                    }),

                DatePicker::make('date')
                    ->native(false)
                    ->required(),

                TimePicker::make('from_time')
                    ->required()
                    ->reactive(), // التحديث التلقائي عند التغيير

                TimePicker::make('end_time')
                    ->required()
                    ->reactive()
                    ->afterStateUpdated(function ($state, callable $get, callable $set) {
                        $fromTime = $get('from_time');
                        $endTime = $get('end_time');

                        if ($fromTime && $endTime) {
                            // حساب الفرق بالساعات
                            $fromTime = \Carbon\Carbon::parse($fromTime);
                            $endTime = \Carbon\Carbon::parse($endTime);

                            $differenceInHours = $fromTime->diffInMinutes($endTime) / 60;


                            // dd($differenceInHours);
            
                            // تحديث قيمة fees بناءً على الفرق بالساعات
                            $fees = User::find($get('trainer_id'))->fees;
                            if ($differenceInHours <= 4) {
                                $set('fees', $fees / 2); // نصف القيمة
                            } else {
                                $set('fees', $fees); // القيمة الكاملة
                            }
                        }
                    }),

                Select::make('payment_method')
                    ->options([
                        'cash' => 'Cash',
                        'credit' => 'Credit',
                        'cheque' => 'Cheque',
                    ]),

                TextInput::make('fees')
                    ->required(), // تعطيل الإدخال اليدوي
                    ])
                    ->requiresConfirmation(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }
}
