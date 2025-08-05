<?php

namespace App\Filament\Pages\Settings;

use App\Settings\RecoverySettings;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Pages\Page;
use Filament\Actions\Action;
use Filament\Notifications\Notification;

class RecoverySettingsPage extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-currency-dollar';
    protected static ?string $navigationLabel = 'Recouvrement';
    protected static ?string $title = 'Configuration du recouvrement';
    protected static ?string $navigationGroup = 'Configuration';
    protected static ?int $navigationSort = 6;

    protected static string $view = 'filament.pages.settings.recovery-settings';

    public $data = [];

    public function mount()
    {
        $settings = app(RecoverySettings::class);
        $this->data = [
            'recovery_reminder1_days' => $settings->recovery_reminder1_days,
            'recovery_reminder2_days' => $settings->recovery_reminder2_days,
            'recovery_mise_en_demeure_days' => $settings->recovery_mise_en_demeure_days,
            'recovery_litigation_threshold_amount' => $settings->recovery_litigation_threshold_amount,
            'mise_en_demeure_template_text' => $settings->mise_en_demeure_template_text,
            'enable_automatic_reminders' => $settings->enable_automatic_reminders,
            'send_email_notifications' => $settings->send_email_notifications,
            'send_sms_notifications' => $settings->send_sms_notifications,
            'enable_late_payment_interest' => $settings->enable_late_payment_interest,
            'late_payment_interest_rate' => $settings->late_payment_interest_rate,
            'auto_escalate_to_litigation' => $settings->auto_escalate_to_litigation,
            'litigation_contact_email' => $settings->litigation_contact_email,
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
        $settings = app(RecoverySettings::class);

        $settings->recovery_reminder1_days = $this->data['recovery_reminder1_days'];
        $settings->recovery_reminder2_days = $this->data['recovery_reminder2_days'];
        $settings->recovery_mise_en_demeure_days = $this->data['recovery_mise_en_demeure_days'];
        $settings->recovery_litigation_threshold_amount = $this->data['recovery_litigation_threshold_amount'];
        $settings->mise_en_demeure_template_text = $this->data['mise_en_demeure_template_text'];
        $settings->enable_automatic_reminders = $this->data['enable_automatic_reminders'];
        $settings->send_email_notifications = $this->data['send_email_notifications'];
        $settings->send_sms_notifications = $this->data['send_sms_notifications'];
        $settings->enable_late_payment_interest = $this->data['enable_late_payment_interest'];
        $settings->late_payment_interest_rate = $this->data['late_payment_interest_rate'];
        $settings->auto_escalate_to_litigation = $this->data['auto_escalate_to_litigation'];
        $settings->litigation_contact_email = $this->data['litigation_contact_email'];

        $settings->save();

        Notification::make()
            ->title('Configuration du recouvrement sauvegardée')
            ->success()
            ->send();
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Tabs::make('Configuration du recouvrement')
                    ->tabs([
                        Forms\Components\Tabs\Tab::make('Délais et procédures')
                            ->schema([
                                Forms\Components\Section::make('Délais des rappels')
                                    ->description('Configuration des délais pour les différentes étapes du recouvrement')
                                    ->schema([
                                        Forms\Components\Grid::make(3)
                                            ->schema([
                                                Forms\Components\TextInput::make('recovery_reminder1_days')
                                                    ->label('Premier rappel (jours)')
                                                    ->numeric()
                                                    ->required()
                                                    ->minValue(1)
                                                    ->maxValue(365)
                                                    ->helperText('Jours après échéance pour le 1er rappel'),

                                                Forms\Components\TextInput::make('recovery_reminder2_days')
                                                    ->label('Deuxième rappel (jours)')
                                                    ->numeric()
                                                    ->required()
                                                    ->minValue(1)
                                                    ->maxValue(365)
                                                    ->helperText('Jours après 1er rappel pour le 2e rappel'),

                                                Forms\Components\TextInput::make('recovery_mise_en_demeure_days')
                                                    ->label('Mise en demeure (jours)')
                                                    ->numeric()
                                                    ->required()
                                                    ->minValue(1)
                                                    ->maxValue(365)
                                                    ->helperText('Jours après 2e rappel pour mise en demeure'),
                                            ]),
                                    ]),

                                Forms\Components\Section::make('Seuil pour contentieux')
                                    ->schema([
                                        Forms\Components\Grid::make(2)
                                            ->schema([
                                                Forms\Components\TextInput::make('recovery_litigation_threshold_amount')
                                                    ->label('Montant seuil pour contentieux')
                                                    ->numeric()
                                                    ->required()
                                                    ->minValue(0)
                                                    ->prefix('GNF')
                                                    ->helperText('Montant au-delà duquel un dossier peut passer en contentieux'),

                                                Forms\Components\TextInput::make('litigation_contact_email')
                                                    ->label('Email contact contentieux')
                                                    ->email()
                                                    ->required()
                                                    ->helperText('Email pour les notifications de contentieux'),
                                            ]),
                                    ]),
                            ]),

                        Forms\Components\Tabs\Tab::make('Notifications')
                            ->schema([
                                Forms\Components\Section::make('Paramètres de notification')
                                    ->schema([
                                        Forms\Components\Toggle::make('enable_automatic_reminders')
                                            ->label('Activer les rappels automatiques')
                                            ->helperText('Envoyer automatiquement les rappels selon les délais configurés')
                                            ->live()
                                            ->columnSpanFull(),

                                        Forms\Components\Grid::make(2)
                                            ->schema([
                                                Forms\Components\Toggle::make('send_email_notifications')
                                                    ->label('Notifications par email')
                                                    ->helperText('Envoyer les rappels par email'),

                                                Forms\Components\Toggle::make('send_sms_notifications')
                                                    ->label('Notifications par SMS')
                                                    ->helperText('Envoyer les rappels par SMS'),
                                            ])
                                            ->hidden(fn (callable $get) => !$get('enable_automatic_reminders')),
                                    ]),
                            ]),

                        Forms\Components\Tabs\Tab::make('Intérêts et escalade')
                            ->schema([
                                Forms\Components\Section::make('Intérêts de retard')
                                    ->schema([
                                        Forms\Components\Toggle::make('enable_late_payment_interest')
                                            ->label('Calculer les intérêts de retard')
                                            ->helperText('Appliquer des intérêts sur les créances en retard')
                                            ->live()
                                            ->columnSpanFull(),

                                        Forms\Components\TextInput::make('late_payment_interest_rate')
                                            ->label('Taux d\'intérêt annuel (%)')
                                            ->numeric()
                                            ->required()
                                            ->minValue(0)
                                            ->maxValue(100)
                                            ->suffix('%')
                                            ->helperText('Taux d\'intérêt annuel appliqué aux retards')
                                            ->hidden(fn (callable $get) => !$get('enable_late_payment_interest')),
                                    ]),

                                Forms\Components\Section::make('Escalade automatique')
                                    ->schema([
                                        Forms\Components\Toggle::make('auto_escalate_to_litigation')
                                            ->label('Escalade automatique vers contentieux')
                                            ->helperText('Transférer automatiquement les dossiers dépassant le seuil vers le contentieux')
                                            ->columnSpanFull(),
                                    ]),
                            ]),

                        Forms\Components\Tabs\Tab::make('Modèles')
                            ->schema([
                                Forms\Components\Section::make('Modèle de mise en demeure')
                                    ->schema([
                                        Forms\Components\RichEditor::make('mise_en_demeure_template_text')
                                            ->label('Texte du modèle de mise en demeure')
                                            ->required()
                                            ->helperText('Variables disponibles: [MONTANT], [DATE_ECHEANCE], [NOM_DEBITEUR], [REFERENCE]')
                                            ->columnSpanFull(),
                                    ]),
                            ]),
                    ]),
            ])
            ->statePath('data');
    }
}
