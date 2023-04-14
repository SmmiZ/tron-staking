<?php

namespace App\Http\Controllers;

use App\Models\{Order, User};
use Illuminate\Contracts\View\View;

class ExecutorController extends Controller
{
    public function __construct()
    {
        $this->authorizeResource(User::class);
    }

    public function index(Order $order): View
    {
        return view('orders.executors.index', compact('order'));
    }
}
