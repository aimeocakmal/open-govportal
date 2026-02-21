<?php

namespace App\Filament\Resources\Users\Pages;

use App\Filament\Resources\Users\UserResource;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Facades\Hash;

class EditUser extends EditRecord
{
    protected static string $resource = UserResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('toggleActive')
                ->label(fn () => $this->record->is_active ? __('filament.actions.deactivate') : __('filament.actions.reactivate'))
                ->color(fn () => $this->record->is_active ? 'danger' : 'success')
                ->icon(fn () => $this->record->is_active ? 'heroicon-o-x-circle' : 'heroicon-o-check-circle')
                ->requiresConfirmation()
                ->action(function () {
                    $this->record->update(['is_active' => ! $this->record->is_active]);
                    $this->refreshFormData(['is_active']);
                }),
            Action::make('resetPassword')
                ->color('warning')
                ->icon('heroicon-o-key')
                ->requiresConfirmation()
                ->form([
                    \Filament\Forms\Components\TextInput::make('new_password')
                        ->password()
                        ->revealable()
                        ->required()
                        ->minLength(8),
                ])
                ->action(function (array $data) {
                    $this->record->update(['password' => Hash::make($data['new_password'])]);
                    \Filament\Notifications\Notification::make()
                        ->success()
                        ->title(__('filament.actions.password_reset_successfully'))
                        ->send();
                }),
            DeleteAction::make(),
        ];
    }
}
