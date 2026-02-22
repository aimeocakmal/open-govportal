<?php

namespace App\Filament\Pages\Auth;

use Filament\Actions\Action;
use Filament\Auth\Pages\EditProfile as BaseEditProfile;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Notifications\Notification;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;

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
                    ->directory('avatars')
                    ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/gif', 'image/webp'])
                    ->maxSize(1024)
                    ->placeholder(__('filament.common.file_upload_placeholder'))
                    ->helperText(__('filament.common.upload_help_avatar', ['size' => '1 MB'])),
                Select::make('preferred_locale')
                    ->label(__('filament.profile.language'))
                    ->options([
                        'ms' => __('filament.common.bahasa_malaysia'),
                        'en' => __('filament.common.english'),
                    ]),
                $this->getPasswordFormComponent(),
                $this->getPasswordConfirmationFormComponent(),
            ]);
    }

    public function content(Schema $schema): Schema
    {
        $components = [
            $this->getFormContentComponent(),
            ...Arr::wrap($this->getMultiFactorAuthenticationContentComponent()),
        ];

        $user = Auth::user();

        if ($user && ! $user->hasRole('super_admin')) {
            $components[] = $this->getDangerZoneSection();
        }

        return $schema->components($components);
    }

    protected function getDangerZoneSection(): Section
    {
        return Section::make(__('filament.profile.danger_zone'))
            ->description(__('filament.profile.danger_zone_desc'))
            ->schema([
                \Filament\Schemas\Components\Actions::make([
                    Action::make('deleteAccount')
                        ->label(__('filament.profile.delete_account'))
                        ->color('danger')
                        ->icon('heroicon-o-trash')
                        ->requiresConfirmation()
                        ->modalHeading(__('filament.profile.delete_account'))
                        ->modalDescription(__('filament.profile.delete_account_warning'))
                        ->modalSubmitActionLabel(__('filament.profile.delete_account_confirm'))
                        ->action(function (): void {
                            $user = Auth::user();
                            Auth::logout();
                            $user->update(['is_active' => false]);
                            $user->delete();

                            session()->invalidate();
                            session()->regenerateToken();

                            Notification::make()
                                ->success()
                                ->title(__('filament.profile.account_deleted'))
                                ->send();

                            $this->redirect(filament()->getLoginUrl());
                        }),
                ]),
            ])
            ->collapsed(false);
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
}
