<?php

namespace App\Http\Controllers;

use Illuminate\View\View;

class SearchController extends Controller
{
    public function index(string $locale): View
    {
        return view('carian.index');
    }
}
