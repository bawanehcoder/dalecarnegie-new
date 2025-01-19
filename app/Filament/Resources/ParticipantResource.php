<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ParticipantResource\Pages;
use App\Filament\Resources\ParticipantResource\RelationManagers;
use App\Models\Company;
use App\Models\CompanyUser;
use App\Models\Participant;
use App\Models\Trainee;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\SpatieMediaLibraryFileUpload;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\BooleanColumn;
use Filament\Tables\Columns\SpatieMediaLibraryImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Enums\FiltersLayout;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class ParticipantResource extends Resource
{
    protected static ?string $model = Trainee::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static ?string $navigationLabel = 'Participant';
    protected static ?string $label = 'Participant';

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->where('type', 0);
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('name')
                    ->required()
                    ->maxLength(255),

                    TextInput::make('email')
                    ->email(),
                
                TextInput::make('phone')
                    ->tel()
                    ->maxLength(15),
                Select::make('gender')
                    ->options([
                        '0' => 'Male',
                        '1' => 'Female',
                    ])
                    ->required(),
                // DatePicker::make('birth_date')
                //     ->required(),
                
                
                Select::make('client')
                    ->label('Client')
                    ->preload()
                    ->searchable()
                    ->relationship('companies', 'name'),
                    Toggle::make('active'),
                
                SpatieMediaLibraryFileUpload::make('certificates')
                    ->multiple()
                    ->collection('users'),
                SpatieMediaLibraryFileUpload::make('image')
                    ->collection('users'),
                
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')->sortable()->searchable(),
                SpatieMediaLibraryImageColumn::make('image'),

                TextColumn::make('company.user.name')
                ->label('Client')
                
                ->getStateUsing(function ($record) {
                    return  Company::find(CompanyUser::where('user_id', $record->id)->first()?->company_id)->name ?? "";
                }),
                TextColumn::make('phone')->sortable()->searchable(),
                BooleanColumn::make('active')->label('Active'),

            ])
            ->filters([
                

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
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListParticipants::route('/'),
            'create' => Pages\CreateParticipant::route('/create'),
            'edit' => Pages\EditParticipant::route('/{record}/edit'),
        ];
    }
}
