<?php

namespace App\Modules\News\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class NewsController extends Controller
{
    public function index(Request $request)
    {
        $news = [];
        return view('pages.news.index', compact('news'));
    }
}
