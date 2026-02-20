<?php

namespace App\Http\Controllers;

use Illuminate\View\View;

class HomeController extends Controller
{
    public function index(string $locale): View
    {
        return view('home.index');
    }
}
