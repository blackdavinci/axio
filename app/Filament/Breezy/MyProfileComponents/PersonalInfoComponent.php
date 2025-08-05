<?php

namespace App\Filament\Breezy\MyProfileComponents;

use Filament\Facades\Filament;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Form;
use Jeffgreco13\FilamentBreezy\Livewire\MyProfileComponent;
use Ysfkaya\FilamentPhoneInput\Forms\PhoneInput;

class PersonalInfoComponent extends MyProfileComponent
{
    protected string $view = "filament-breezy::livewire.personal-info";

    public array $only = ['prenom', 'nom', 'email', 'genre', 'photo', 'telephone', 'telephone_secondaire', 'matricule', 'grade', 'adresse', 'date_naissance', 'categorie', 'specialite', 'personne_urgence', 'telephone_urgence', 'poste'];

    public array $data = [];
    
    public $user;

    public function mount()
    {
        $this->user = Filament::getCurrentPanel()->auth()->user();
        $userData = $this->user->only($this->only);
        $this->form->fill($userData);
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Informations personnelles')
                    ->schema([
                        FileUpload::make('photo')
                            ->label('Photo de profil')
                            ->image()
                            ->imageEditor()
                            ->avatar()
                            ->disk('public')
                            ->directory('users/photos')
                            ->visibility('public')
                            ->columnSpanFull(),

                        Grid::make(2)
                            ->schema([
                                TextInput::make('prenom')
                                    ->label('Prénom')
                                    ->required()
                                    ->maxLength(255),

                                TextInput::make('nom')
                                    ->label('Nom')
                                    ->required()
                                    ->maxLength(255),
                            ]),

                        Grid::make(3)
                            ->schema([
                                Select::make('genre')
                                    ->label('Genre')
                                    ->options([
                                        'M' => 'Masculin',
                                        'F' => 'Féminin',
                                    ])
                                    ->required(),

                                TextInput::make('email')
                                    ->label('Email')
                                    ->email()
                                    ->required()
                                    ->unique(ignoreRecord: true)
                                    ->maxLength(255),

                                TextInput::make('matricule')
                                    ->label('Matricule')
                                    ->unique(ignoreRecord: true)
                                    ->maxLength(255),
                            ]),

                        Grid::make(2)
                            ->schema([
                                PhoneInput::make('telephone')
                                    ->label('Téléphone principal')
                                    ->defaultCountry('GN')
                                    ->validateFor(lenient: true)
                                    ->required(),

                                PhoneInput::make('telephone_secondaire')
                                    ->label('Téléphone secondaire')
                                    ->defaultCountry('GN')
                                    ->validateFor(lenient: true),
                            ]),

                        Textarea::make('adresse')
                            ->label('Adresse')
                            ->rows(3)
                            ->maxLength(500)
                            ->columnSpanFull(),
                    ]),

                Section::make('Informations professionnelles')
                    ->schema([
                        Grid::make(3)
                            ->schema([
                                TextInput::make('grade')
                                    ->label('Grade')
                                    ->maxLength(255),

                                TextInput::make('poste')
                                    ->label('Poste/Fonction')
                                    ->maxLength(255),

                                Select::make('categorie')
                                    ->label('Catégorie')
                                    ->options([
                                        'fonctionnaire' => 'Fonctionnaire',
                                        'contractuel' => 'Contractuel',
                                        'consultant' => 'Consultant',
                                        'stagiaire' => 'Stagiaire',
                                    ]),
                            ]),

                        Grid::make(2)
                            ->schema([
                                TextInput::make('specialite')
                                    ->label('Spécialité')
                                    ->maxLength(255),

                                DatePicker::make('date_naissance')
                                    ->label('Date de naissance')
                                    ->maxDate(now()->subYears(16)),
                            ]),
                    ]),

                Section::make('Contact d\'urgence')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextInput::make('personne_urgence')
                                    ->label('Personne à contacter')
                                    ->maxLength(255),

                                PhoneInput::make('telephone_urgence')
                                    ->label('Téléphone urgence')
                                    ->defaultCountry('GN')
                                    ->validateFor(lenient: true),
                            ]),
                    ]),
            ]);
    }

    public function submit(): void
    {
        $data = $this->form->getState();
        $this->user->update($data);
        $this->notify('success', __('filament-breezy::default.profile.personal_info.notify'));
    }
}