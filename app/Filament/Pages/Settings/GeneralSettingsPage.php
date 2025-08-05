<?php

namespace App\Filament\Pages\Settings;

use App\Settings\GeneralSettings;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Pages\Page;
use Filament\Actions\Action;
use Filament\Notifications\Notification;

class GeneralSettingsPage extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-cog-6-tooth';
    protected static ?string $navigationLabel = 'Générale';
    protected static ?string $title = 'Configuration générale';
    protected static ?string $navigationGroup = 'Configuration';
    protected static ?int $navigationSort = 1;

    protected static string $view = 'filament.pages.settings.general-settings';

    public $data = [];

    public function mount()
    {
        $settings = app(GeneralSettings::class);
        $this->data = [
            'organization_name' => $settings->organization_name,
            'organization_short_name' => $settings->organization_short_name,
            'organization_logo' => $settings->organization_logo,
            'organization_favicon' => $settings->organization_favicon,
            'organization_address' => $settings->organization_address,
            'organization_phone' => $settings->organization_phone,
            'organization_email' => $settings->organization_email,
            'timezone' => $settings->timezone,
            'language' => $settings->language,
            'date_format' => $settings->date_format,
            'organization_website' => $settings->organization_website,
            'organization_description' => $settings->organization_description,
            'enable_document_archiving' => $settings->enable_document_archiving,
            'document_retention_years' => $settings->document_retention_years,
            'auto_archive_after_retention' => $settings->auto_archive_after_retention,
            'archive_storage_path' => $settings->archive_storage_path,
            'compress_archived_documents' => $settings->compress_archived_documents,
            'archivable_document_types' => $settings->archivable_document_types,
        ];
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('save')
                ->label('Enregistrer')
                ->color('primary')
                ->action('save'),
        ];
    }

    public function save()
    {
        $settings = app(GeneralSettings::class);

        $settings->organization_name = $this->data['organization_name'];
        $settings->organization_short_name = $this->data['organization_short_name'];
        $settings->organization_logo = $this->data['organization_logo'];
        $settings->organization_favicon = $this->data['organization_favicon'];
        $settings->organization_address = $this->data['organization_address'];
        $settings->organization_phone = $this->data['organization_phone'];
        $settings->organization_email = $this->data['organization_email'];
        $settings->timezone = $this->data['timezone'];
        $settings->language = $this->data['language'];
        $settings->date_format = $this->data['date_format'];
        $settings->organization_website = $this->data['organization_website'];
        $settings->organization_description = $this->data['organization_description'];
        $settings->enable_document_archiving = $this->data['enable_document_archiving'];
        $settings->document_retention_years = $this->data['document_retention_years'];
        $settings->auto_archive_after_retention = $this->data['auto_archive_after_retention'];
        $settings->archive_storage_path = $this->data['archive_storage_path'];
        $settings->compress_archived_documents = $this->data['compress_archived_documents'];
        $settings->archivable_document_types = $this->data['archivable_document_types'];

        $settings->save();

        Notification::make()
            ->title('Configuration sauvegardée')
            ->success()
            ->send();
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Tabs::make('Configuration')
                    ->tabs([
                        Forms\Components\Tabs\Tab::make('Organisation')
                            ->schema([
                                Forms\Components\Section::make('Informations de l\'organisation')
                                    ->schema([
                                        Forms\Components\Grid::make(2)
                                            ->schema([
                                                Forms\Components\TextInput::make('organization_name')
                                                    ->label('Nom de l\'organisation')
                                                    ->required()
                                                    ->maxLength(255),

                                                Forms\Components\TextInput::make('organization_short_name')
                                                    ->label('Nom abrégé')
                                                    ->helperText('Ex: RG, ONPG, etc.')
                                                    ->maxLength(10),
                                            ]),

                                        Forms\Components\Grid::make(2)
                                            ->schema([
                                                Forms\Components\FileUpload::make('organization_logo')
                                                    ->label('Logo officiel')
                                                    ->image()
                                                    ->imageEditor()
                                                    ->disk('public')
                                                    ->directory('settings/logos')
                                                    ->visibility('public'),

                                                Forms\Components\FileUpload::make('organization_favicon')
                                                    ->label('Favicon')
                                                    ->image()
                                                    ->imageEditor()
                                                    ->disk('public')
                                                    ->directory('settings/favicons')
                                                    ->visibility('public'),
                                            ]),

                                        Forms\Components\Textarea::make('organization_description')
                                            ->label('Description de l\'organisation')
                                            ->rows(3)
                                            ->maxLength(500)
                                            ->columnSpanFull(),

                                        Forms\Components\TextInput::make('organization_website')
                                            ->label('Site web')
                                            ->url()
                                            ->maxLength(255)
                                            ->columnSpanFull(),
                                    ])->columns(2),

                                Forms\Components\Section::make('Coordonnées')
                                    ->schema([
                                        Forms\Components\Textarea::make('organization_address')
                                            ->label('Adresse complète')
                                            ->required()
                                            ->rows(3)
                                            ->maxLength(500)
                                            ->columnSpanFull(),

                                        Forms\Components\Grid::make(2)
                                            ->schema([
                                                Forms\Components\TextInput::make('organization_phone')
                                                    ->label('Téléphone principal')
                                                    ->tel()
                                                    ->required()
                                                    ->maxLength(255),

                                                Forms\Components\TextInput::make('organization_email')
                                                    ->label('Email principal')
                                                    ->email()
                                                    ->required()
                                                    ->maxLength(255),
                                            ]),
                                    ]),

                                Forms\Components\Section::make('Paramètres régionaux')
                                    ->schema([
                                        Forms\Components\Select::make('timezone')
                                            ->label('Fuseau horaire')
                                            ->options([
                                                'Africa/Conakry' => 'Africa/Conakry (GMT+0)',
                                                'Africa/Abidjan' => 'Africa/Abidjan (GMT+0)',
                                                'Europe/Paris' => 'Europe/Paris (GMT+1)',
                                            ])
                                            ->required(),

                                        Forms\Components\Select::make('language')
                                            ->label('Langue par défaut')
                                            ->options([
                                                'fr' => 'Français',
                                                'en' => 'English',
                                            ])
                                            ->required(),

                                        Forms\Components\Select::make('date_format')
                                            ->label('Format de date')
                                            ->options([
                                                'd/m/Y' => 'DD/MM/YYYY (31/12/2024)',
                                                'm/d/Y' => 'MM/DD/YYYY (12/31/2024)',
                                                'Y-m-d' => 'YYYY-MM-DD (2024-12-31)',
                                            ])
                                            ->required(),
                                    ])->columns(3),
                            ]),

                        Forms\Components\Tabs\Tab::make('Archivage')
                            ->schema([
                                Forms\Components\Section::make('Configuration de l\'archivage')
                                    ->description('Paramètres pour la gestion automatique de l\'archivage des documents')
                                    ->schema([
                                        Forms\Components\Toggle::make('enable_document_archiving')
                                            ->label('Activer l\'archivage automatique')
                                            ->helperText('Active ou désactive l\'archivage automatique des documents')
                                            ->live()
                                            ->columnSpanFull(),

                                        Forms\Components\Grid::make(2)
                                            ->schema([
                                                Forms\Components\TextInput::make('document_retention_years')
                                                    ->label('Durée de conservation (années)')
                                                    ->numeric()
                                                    ->required()
                                                    ->minValue(1)
                                                    ->maxValue(50)
                                                    ->helperText('Nombre d\'années avant archivage'),

                                                Forms\Components\TextInput::make('archive_storage_path')
                                                    ->label('Chemin de stockage des archives')
                                                    ->required()
                                                    ->helperText('Dossier où seront stockées les archives'),
                                            ])
                                            ->hidden(fn (callable $get) => !$get('enable_document_archiving')),

                                        Forms\Components\Grid::make(2)
                                            ->schema([
                                                Forms\Components\Toggle::make('auto_archive_after_retention')
                                                    ->label('Archivage automatique')
                                                    ->helperText('Archiver automatiquement après la période de rétention'),

                                                Forms\Components\Toggle::make('compress_archived_documents')
                                                    ->label('Compression des documents')
                                                    ->helperText('Compresser les documents archivés pour économiser l\'espace'),
                                            ])
                                            ->hidden(fn (callable $get) => !$get('enable_document_archiving')),

                                        Forms\Components\TagsInput::make('archivable_document_types')
                                            ->label('Types de documents archivables')
                                            ->helperText('Extensions de fichiers qui peuvent être archivés (ex: pdf, doc, jpg)')
                                            ->placeholder('pdf')
                                            ->hidden(fn (callable $get) => !$get('enable_document_archiving'))
                                            ->columnSpanFull(),
                                    ]),
                            ]),
                    ]),
            ])
            ->statePath('data');
    }
}
