<?php

namespace App\Filament\Pages\Auth;

use Filament\Auth\Pages\EditProfile as BaseEditProfile;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\App;

class EditProfile extends BaseEditProfile
{
    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                $this->getNameFormComponent(),
                $this->getEmailFormComponent(),
                FileUpload::make('avatar')
                    ->image()
                    ->avatar()
                    ->directory('avatars'),
                Select::make('preferred_locale')
                    ->label('Language / Bahasa')
                    ->options([
                        'ms' => 'Bahasa Malaysia',
                        'en' => 'English',
                    ]),
                $this->getPasswordFormComponent(),
                $this->getPasswordConfirmationFormComponent(),
            ]);
    }

    protected function afterSave(): void
    {
        $locale = $this->getUser()->preferred_locale;

        if ($locale && $locale !== App::getLocale()) {
            App::setLocale($locale);
        }
    }

    protected function getRedirectUrl(): ?string
    {
        return filament()->getProfileUrl();
    }

    protected function getDeleteAccountAction(): ?\Filament\Actions\Action
    {
        if (auth()->user()?->hasRole('super_admin')) {
            return null;
        }

        return parent::getDeleteAccountAction();
    }
}
