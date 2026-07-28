<?php

namespace App\Http\Controllers;

use App\Models\CrabSpecies;
use Illuminate\Support\Facades\Auth;

class HomeController extends Controller
{
    public function index()
    {
        if (Auth::check()) {
            return redirect()->route('dashboard');
        }

        return view('welcome', ['species' => CrabSpecies::where('is_active', true)->latest()->limit(6)->get()]);
    }
}
