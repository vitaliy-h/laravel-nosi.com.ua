<?php

namespace App\Modules\Auth\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class PasswordResetController extends Controller
{
    public function index(Request $request)
    {
        return view('pages.auth.password_reset');
    }


    public function store(Request $request): RedirectResponse
    {

    }
}
