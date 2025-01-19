<?php

namespace App\Filament\Resources\ProgramResource\RelationManagers;

use App\Imports\ParticipantsImport;
use App\Models\CompanyUser;
use App\Models\Course;
use App\Models\CoursesTrainee;
use App\Models\Payment;
use App\Models\Trainee;
use Filament\Actions\Action;
use Filament\Forms;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\SpatieMediaLibraryFileUpload;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Maatwebsite\Excel\Facades\Excel;

class ParticipantRelationManager extends RelationManager
{
    protected static string $relationship = 'trainees';

    protected static ?string $label = "Participant";


    protected static ?string $pluralLabel = 'Participants';

    public function form(Form $form): Form
    {
        // dd($this->ownerRecord);
        return $form
            ->schema([
                Select::make('user_id')
                    ->label('Participant')
                    ->relationship('user', 'name')
                    ->preload()
                    ->searchable()
                    ->columnSpan(function () {
                        return $this->ownerRecord->type == 'public' ? 1 : 2;
                    })
                    ->createOptionForm([
                        TextInput::make('name')
                            ->required()
                            ->maxLength(255),
                        
                        TextInput::make('phone')
                            ->tel()
                            ->maxLength(15),
                        Select::make('gender')
                            ->options([
                                '0' => 'Male',
                                '1' => 'Female',
                            ])
                            ->required(),
                        DatePicker::make('birth_date')
                            ->required(),
                       
                        
                        Select::make('client')
                            ->label('Client')
                            ->preload()
                            ->searchable()
                            ->relationship('companies', 'name'),

                        SpatieMediaLibraryFileUpload::make('certificates')
                            ->multiple()
                            ->collection('users'),
                        SpatieMediaLibraryFileUpload::make('image')
                            ->collection('users'),
                        Toggle::make('active'),
                    ])
                    ->required(),

                Forms\Components\TextInput::make('price')
                    ->required()
                    ->visible(fn() => $this->ownerRecord->type == 'public')
                    ->maxLength(255),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('id')
            ->columns([
                Tables\Columns\TextColumn::make('user.name'),
                Tables\Columns\TextColumn::make('price')->visible(fn() => $this->ownerRecord->type == 'public'),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make(),
                \Filament\Tables\Actions\Action::make('importt')
                    ->label('Import Participant')
                    ->form([
                        // View::make('components.download-sample') // لعرض زر التحميل
                        // ->label('تحميل مثال ملف Excel'),

                        Hidden::make('course_id')->default($this->ownerRecord->id),
                        Hidden::make('company_id')->default($this->ownerRecord->company_id),

                        FileUpload::make('file')
                            ->label('Select CSV File')
                            ->directory('temp') // Save the file temporarily
                            ->required()
                            ->acceptedFileTypes(['text/csv', 'text/plain', 'application/vnd.ms-excel', 'application/csv', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet']),
                    ])
                    ->action(function (array $data,$action) {
                        // Get the relative file path from the FileUpload component
                        $relativePath = 'app/public/' . $data['file'];
                        $filePath = storage_path($relativePath); // Builds the full path
            
                        //Check if the file exists before importing
                        if (!file_exists($filePath)) {
                            Notification::make()
                                ->title('File does not exist for import.')
                                ->danger()
                                ->send();
                            return;
                        }
                        $importer = new ParticipantsImport($data);
                        Excel::import($importer, $filePath);
                        
                        // dd("alaa2");
                        Notification::make()
                            ->title('Orders imported successfully')
                            ->success()
                            ->send();

                            $action->cancel();

                            

                        


                    }),

            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
                \Filament\Tables\Actions\Action::make('payment')
                    ->label('Payment')
                    ->form(function(CoursesTrainee $item){
                        return [
                     
                            // Hidden::make('company_id')->default($this->ownerRecord->company_id),
                            DatePicker::make('date')->native(false)->required(),
                            TextInput::make('amount')->numeric()->required(),
                            TextInput::make('invoice_id')->label('Invoice Number')->required(),
                            
                            Hidden::make('course_id')->default($this->ownerRecord->id),
                            Hidden::make('entity_id')->default($item->id),
                            Hidden::make('entity_type')->default('trainee'),
                        ];
                    })
                    ->action(function ($data, CoursesTrainee $item){
                        $payment = new Payment();
                        $payment->date = $data['date'];
                        $payment->amount = $data['amount'];
                        $payment->invoice_id = $data['invoice_id'];
                        $payment->course_id = $data['course_id'];
                        $payment->entity_id = $data['entity_id'];
                        $payment->entity_type = $data['entity_type'];
                        $payment->save();
                        Notification::make()
                            ->title('Payment added successfully')
                            ->success()
                            ->send();
                    }),

            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }
}
