<?php

namespace App\Controllers;

class Auth extends BaseController
{
    public function login(): string
    {
        return view(name: 'auth/login');
    }

    public function resetPassword(): string
    {
        return view(name: 'auth/reset_password');
    }

    public function contactAdministrator(): string
    {
        return view(name: 'auth/contact_admin');
    }

}