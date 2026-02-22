<?php

namespace App\Http\Controllers;

use Illuminate\View\View;

class DirectoriController extends Controller
{
    public function index(string $locale): View
    {
        return view('direktori.index');
    }
}
