<?php

namespace App\Http\Controllers;

use Illuminate\View\View;

class HomeController extends Controller
{
    /**
     * Возвращает главную страницу
     *
     * @return View
     */
    public function index(): View
    {
        return view('home');
    }
}
