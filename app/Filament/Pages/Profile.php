<?php

namespace App\Filament\Pages;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class Profile extends Page implements HasForms
{
    use InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-user-circle';
    protected static ?string $navigationLabel = 'My Profile';
    protected static ?string $title = 'My Profile';
    protected static string $view = 'filament.pages.profile';

    public ?array $infoData = [];
    public ?array $passwordData = [];

    public function mount(): void
    {
        $this->infoForm->fill([
            'name' => Auth::user()->name,
            'email' => Auth::user()->email,
            'phone' => Auth::user()->phone,
            'address' => Auth::user()->address,
        ]);
    }

    protected function getForms(): array
    {
        return [
            'infoForm',
            'passwordForm',
        ];
    }

    public function infoForm(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('name')->label('Full Name')->required(),
                TextInput::make('email')->label('Email')->email()->required()->unique(ignoreRecord: true),
                TextInput::make('phone')->label('Phone')->tel(),
                TextInput::make('address')->label('Address'),
            ])
            ->statePath('infoData');
    }

    public function passwordForm(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('current_password')
                    ->label('Current Password')
                    ->password()
                    ->required()
                    ->rule('current_password'),
                TextInput::make('new_password')
                    ->label('New Password')
                    ->password()
                    ->required()
                    ->minLength(6)
                    ->same('new_password_confirmation'),
                TextInput::make('new_password_confirmation')
                    ->label('Confirm New Password')
                    ->password()
                    ->required(),
            ])
            ->statePath('passwordData');
    }

    public function saveInfo(): void
    {
        $data = $this->infoForm->getState();

        $user = Auth::user();
        $user->name = $data['name'];
        $user->email = $data['email'];
        $user->phone = $data['phone'] ?? null;
        $user->address = $data['address'] ?? null;
        $user->save();

        Notification::make()->title('Profile updated')->success()->send();
    }

    public function savePassword(): void
    {
        $data = $this->passwordForm->getState();

        $user = Auth::user();
        $user->password = Hash::make($data['new_password']);
        $user->save();

        $this->passwordForm->fill();

        Notification::make()->title('Password changed')->success()->send();
    }

    public static function shouldRegisterNavigation(): bool
    {
        return false;
    }
}
