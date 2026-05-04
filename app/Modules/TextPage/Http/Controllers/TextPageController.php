<?php

namespace App\Modules\TextPage\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class TextPageController extends Controller
{
    public function index(Request $request)
    {
        return view('pages.text_page');
    }
}
