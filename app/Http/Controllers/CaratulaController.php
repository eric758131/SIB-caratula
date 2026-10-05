<?php

namespace App\Http\Controllers;

class CaratulaController extends Controller
{
    /**
     * Entrada del panel: el asistente público es la app de Angular,
     * así que aquí solo se redirige al dashboard o al inicio de sesión.
     */
    public function index()
    {
        return redirect()->route(auth()->check() ? 'dashboard' : 'login');
    }
}
