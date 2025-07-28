<?php

namespace App\Settings;

use Spatie\LaravelSettings\Settings;

class GeneralSettings extends Settings
{
    public string $organization_name;
    public ?string $organization_short_name;
    public ?string $organization_logo;
    public ?string $organization_favicon;
    public string $organization_address;
    public string $organization_phone;
    public string $organization_email;
    public string $timezone;
    public string $language;
    public string $date_format;
    public ?string $organization_website;
    public ?string $organization_description;
    
    // Paramètres d'archivage
    public bool $enable_document_archiving;
    public int $document_retention_years;
    public bool $auto_archive_after_retention;
    public string $archive_storage_path;
    public bool $compress_archived_documents;
    public array $archivable_document_types;

    public static function group(): string
    {
        return 'general';
    }

    public static function defaults(): array
    {
        return [
            'organization_name' => 'République de Guinée',
            'organization_short_name' => 'RG',
            'organization_logo' => null,
            'organization_favicon' => null,
            'organization_address' => 'Conakry, République de Guinée',
            'organization_phone' => '+224',
            'organization_email' => 'contact@gouv.gn',
            'timezone' => 'Africa/Conakry',
            'language' => 'fr',
            'date_format' => 'd/m/Y',
            'organization_website' => null,
            'organization_description' => null,
            // Archivage
            'enable_document_archiving' => true,
            'document_retention_years' => 10,
            'auto_archive_after_retention' => false,
            'archive_storage_path' => 'archives',
            'compress_archived_documents' => true,
            'archivable_document_types' => ['pdf', 'doc', 'docx', 'txt', 'jpg', 'png'],
        ];
    }
}