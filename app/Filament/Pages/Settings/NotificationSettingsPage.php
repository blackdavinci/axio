<?php

namespace App\Filament\Pages\Settings;

use App\Settings\NotificationSettings;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Pages\Page;
use Filament\Actions\Action;
use Filament\Notifications\Notification;

class NotificationSettingsPage extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-bell';
    protected static ?string $navigationLabel = 'Notifications';
    protected static ?string $title = 'Configuration des notifications';
    protected static ?string $navigationGroup = 'Configuration';
    protected static ?int $navigationSort = 7;

    protected static string $view = 'filament.pages.settings.notification-settings';

    public static function canAccess(): bool
    {
        return true;
    }

    public $data = [];

    public function mount()
    {
        $settings = app(NotificationSettings::class);
        $this->data = [
            'enable_notifications' => $settings->enable_notifications,
            'default_channels' => $settings->default_channels,
            'enable_email_notifications' => $settings->enable_email_notifications,
            'enable_sms_notifications' => $settings->enable_sms_notifications,
            'enable_in_app_notifications' => $settings->enable_in_app_notifications,
            'nimba_api_url' => $settings->nimba_api_url,
            'nimba_api_key' => $settings->nimba_api_key,
            'nimba_sender_id' => $settings->nimba_sender_id,
            'nimba_test_mode' => $settings->nimba_test_mode,
            'notification_signature' => $settings->notification_signature,
            'notification_types_enabled' => $settings->notification_types_enabled,
            'notify_on_courrier_assignment' => $settings->notify_on_courrier_assignment,
            'notify_on_status_change' => $settings->notify_on_status_change,
            'notify_on_deadline_approaching' => $settings->notify_on_deadline_approaching,
            'deadline_reminder_days' => $settings->deadline_reminder_days,
            'notify_on_escalation' => $settings->notify_on_escalation,
            'max_sms_per_day_per_user' => $settings->max_sms_per_day_per_user,
            'group_notifications' => $settings->group_notifications,
        ];
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('save')
                ->label('Enregistrer')
                ->color('primary')
                ->action('save'),

            Action::make('test_sms')
                ->label('Tester SMS')
                ->color('warning')
                ->icon('heroicon-o-device-phone-mobile')
                ->action('testSms')
                ->visible(fn () => $this->data['enable_sms_notifications'] ?? false),
        ];
    }

    public function save()
    {
        $settings = app(NotificationSettings::class);

        foreach ($this->data as $key => $value) {
            $settings->$key = $value;
        }

        $settings->save();

        Notification::make()
            ->title('Configuration des notifications sauvegardée')
            ->success()
            ->send();
    }

    public function testSms()
    {
        try {
            $smsService = app(\App\Services\NimbaSmsService::class);

            // Tester d'abord la connexion à l'API
            $connectionTest = $smsService->testConnection();

            if (!$connectionTest['success']) {
                Notification::make()
                    ->title('Erreur de connexion SMS')
                    ->body($connectionTest['message'])
                    ->danger()
                    ->send();
                return;
            }

            // Envoyer un SMS de test à l'utilisateur connecté
            $user = auth()->user();
            $phoneNumber = $user->telephone ?? '+224000000000'; // Numéro par défaut si pas de téléphone

            $message = "Test de notification SMS depuis Axio.\nConfiguration réussie ✓\nDate: " . now()->format('d/m/Y H:i');

            $result = $smsService->sendSms($phoneNumber, $message);

            if ($result['success']) {
                Notification::make()
                    ->title('Test SMS réussi')
                    ->body("SMS de test envoyé avec succès au {$phoneNumber}")
                    ->success()
                    ->send();

                // Vérifier le solde après l'envoi
                $balance = $smsService->getBalance();
                if ($balance['success']) {
                    Notification::make()
                        ->title('Solde SMS')
                        ->body("Solde actuel: {$balance['balance']} {$balance['currency']}")
                        ->info()
                        ->send();
                }
            } else {
                Notification::make()
                    ->title('Erreur d\'envoi SMS')
                    ->body($result['error'] ?? 'Erreur inconnue')
                    ->danger()
                    ->send();
            }

        } catch (\Exception $e) {
            Notification::make()
                ->title('Erreur lors du test SMS')
                ->body($e->getMessage())
                ->danger()
                ->send();
        }
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Tabs::make('Configuration des notifications')
                    ->tabs([
                        Forms\Components\Tabs\Tab::make('Général')
                            ->schema([
                                Forms\Components\Section::make('Paramètres généraux')
                                    ->schema([
                                        Forms\Components\Toggle::make('enable_notifications')
                                            ->label('Activer les notifications')
                                            ->helperText('Active ou désactive complètement le système de notifications')
                                            ->live()
                                            ->columnSpanFull(),

                                        Forms\Components\Grid::make(3)
                                            ->schema([
                                                Forms\Components\Toggle::make('enable_email_notifications')
                                                    ->label('Notifications par email')
                                                    ->helperText('Envoyer les notifications par email'),

                                                Forms\Components\Toggle::make('enable_in_app_notifications')
                                                    ->label('Notifications dans l\'app')
                                                    ->helperText('Afficher les notifications dans l\'interface'),

                                                Forms\Components\Toggle::make('enable_sms_notifications')
                                                    ->label('Notifications par SMS')
                                                    ->helperText('Envoyer les notifications par SMS')
                                                    ->live(),
                                            ])
                                            ->hidden(fn (callable $get) => !$get('enable_notifications')),

                                        Forms\Components\CheckboxList::make('default_channels')
                                            ->label('Canaux par défaut')
                                            ->options([
                                                'mail' => 'Email',
                                                'database' => 'Dans l\'application',
                                                'sms' => 'SMS',
                                            ])
                                            ->helperText('Canaux utilisés par défaut pour les nouvelles notifications')
                                            ->hidden(fn (callable $get) => !$get('enable_notifications'))
                                            ->columnSpanFull(),
                                    ]),
                            ]),

                        Forms\Components\Tabs\Tab::make('SMS - NimbaSMS')
                            ->schema([
                                Forms\Components\Section::make('Configuration NimbaSMS')
                                    ->description('Configuration de l\'API NimbaSMS pour les notifications SMS')
                                    ->schema([
                                        Forms\Components\Grid::make(2)
                                            ->schema([
                                                Forms\Components\TextInput::make('nimba_api_url')
                                                    ->label('URL de l\'API')
                                                    ->required()
                                                    ->url()
                                                    ->default('https://api.nimbasms.com/v1'),

                                                Forms\Components\TextInput::make('nimba_sender_id')
                                                    ->label('ID de l\'expéditeur')
                                                    ->required()
                                                    ->maxLength(11)
                                                    ->helperText('Nom affiché comme expéditeur (max 11 caractères)')
                                                    ->default('AXIO'),
                                            ]),

                                        Forms\Components\TextInput::make('nimba_api_key')
                                            ->label('Clé API')
                                            ->required()
                                            ->password()
                                            ->helperText('Clé API fournie par NimbaSMS')
                                            ->columnSpanFull(),

                                        Forms\Components\Grid::make(2)
                                            ->schema([
                                                Forms\Components\Toggle::make('nimba_test_mode')
                                                    ->label('Mode test')
                                                    ->helperText('Utiliser l\'environnement de test NimbaSMS'),

                                                Forms\Components\TextInput::make('max_sms_per_day_per_user')
                                                    ->label('Limite SMS par utilisateur/jour')
                                                    ->numeric()
                                                    ->required()
                                                    ->minValue(1)
                                                    ->maxValue(100)
                                                    ->default(10),
                                            ]),
                                    ]),
                            ])
                            ->hidden(fn (callable $get) => !$get('enable_sms_notifications')),

                        Forms\Components\Tabs\Tab::make('Règles métier')
                            ->schema([
                                Forms\Components\Section::make('Types de notifications')
                                    ->schema([
                                        Forms\Components\Toggle::make('notify_on_courrier_assignment')
                                            ->label('Assignation de courrier')
                                            ->helperText('Notifier quand un courrier est assigné à un utilisateur'),

                                        Forms\Components\Toggle::make('notify_on_status_change')
                                            ->label('Changement de statut')
                                            ->helperText('Notifier lors du changement de statut d\'un courrier'),

                                        Forms\Components\Toggle::make('notify_on_deadline_approaching')
                                            ->label('Échéance approchante')
                                            ->helperText('Notifier quand l\'échéance d\'un courrier approche')
                                            ->live(),

                                        Forms\Components\TextInput::make('deadline_reminder_days')
                                            ->label('Jours avant échéance pour rappel')
                                            ->numeric()
                                            ->required()
                                            ->minValue(1)
                                            ->maxValue(30)
                                            ->default(2)
                                            ->hidden(fn (callable $get) => !$get('notify_on_deadline_approaching')),

                                        Forms\Components\Toggle::make('notify_on_escalation')
                                            ->label('Escalade')
                                            ->helperText('Notifier lors d\'une escalade hiérarchique'),

                                        Forms\Components\Toggle::make('group_notifications')
                                            ->label('Grouper les notifications')
                                            ->helperText('Grouper les notifications similaires pour éviter le spam'),
                                    ])->columns(2),
                            ]),

                        Forms\Components\Tabs\Tab::make('Templates')
                            ->schema([
                                Forms\Components\Section::make('Modèles de messages')
                                    ->schema([
                                        Forms\Components\Textarea::make('notification_signature')
                                            ->label('Signature des notifications')
                                            ->required()
                                            ->rows(3)
                                            ->helperText('Signature ajoutée à la fin de toutes les notifications')
                                            ->columnSpanFull(),

                                        Forms\Components\CheckboxList::make('notification_types_enabled')
                                            ->label('Types de notifications activés')
                                            ->options([
                                                'courrier_assigned' => 'Assignation de courrier',
                                                'status_changed' => 'Changement de statut',
                                                'deadline_reminder' => 'Rappel d\'échéance',
                                                'escalation' => 'Escalade',
                                                'user_mentioned' => 'Mention utilisateur',
                                                'task_completed' => 'Tâche terminée',
                                            ])
                                            ->helperText('Choisir les types de notifications à activer')
                                            ->columnSpanFull(),
                                    ]),
                            ]),
                    ]),
            ])
            ->statePath('data');
    }
}
