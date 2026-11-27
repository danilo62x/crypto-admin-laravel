<?php

namespace App\Filament\Auth;

use Filament\Pages\Auth\Login as BaseLogin;

/**
 * Login do painel com as credenciais de DEMO já preenchidas — o visitante só
 * clica em "Entrar". (Fica fora de app/Filament/Pages para não ser
 * auto-descoberto como página de navegação; é registrado via ->login() no
 * AdminPanelProvider.)
 */
class Login extends BaseLogin
{
    public function mount(): void
    {
        parent::mount();

        $this->form->fill([
            'email'    => 'admin@example.com',
            'password' => 'password',
            'remember' => true,
        ]);
    }
}
