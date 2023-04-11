<?php

namespace App\Http\Controllers;


use App\Models\Consumer;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;

class ConsumerController extends Controller
{
    public function __construct()
    {
        $this->authorizeResource(Consumer::class);
    }

    /**
     * Display a listing of the resource.
     *
     * @return View
     */
    public function index(): View
    {
        return view('consumers.index');
    }
}
