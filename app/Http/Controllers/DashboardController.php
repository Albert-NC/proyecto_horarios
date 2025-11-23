<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index()
    {
        // Vista limpia sin estadísticas ni modelos
        return view('dashboard');
    }
}
