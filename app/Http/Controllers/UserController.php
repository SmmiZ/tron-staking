<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Contracts\View\View;

class UserController extends Controller
{
    public function __construct()
    {
        $this->authorizeResource(User::class);
    }

    public function index(): View
    {
        return view('users.index');
    }

    public function show(User $user): View
    {
        $user->load(['stakes', 'consumers', 'leader', 'level']);

        return view('users.show', compact('user'));
    }

    public function uploadConsumersMenu(User $user): View
    {
        return view('consumers.upload', compact('user'));
    }
}
