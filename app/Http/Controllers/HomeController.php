<?php

namespace App\Http\Controllers;

use App\Models\CrabSpecies;

class HomeController extends Controller
{
    public function index()
    {
        return view('welcome', ['species' => CrabSpecies::where('is_active', true)->latest()->limit(6)->get()]);
    }
}
