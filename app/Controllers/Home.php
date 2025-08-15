<?php

namespace App\Controllers;

defined('BASEPATH') or exit('No direct script access allowed');

class Home extends BaseController
{
    public function index(): string
    {
        return view('welcome_message');
    }
}
