<?php

namespace App\Livewire\Admin;

use Livewire\Component;

class Login extends Component
{
    public string $password = '';

    public function login()
    {
        $expected = (string) config('shop.admin_password');
        if ($expected !== '' && hash_equals($expected, $this->password)) {
            session()->regenerate();
            session(['admin' => true]);

            return $this->redirectRoute('admin', navigate: true);
        }
        $this->addError('password', $expected === '' ? 'ADMIN_PASSWORD не задан в настройках Vercel' : 'Неверный пароль');
    }

    public function render()
    {
        return view('livewire.admin.login')->title('Вход');
    }
}
