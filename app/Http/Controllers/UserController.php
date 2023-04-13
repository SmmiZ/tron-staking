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
        return view('users.show', compact('user'));
    }
}
