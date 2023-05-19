<?php

namespace App\Http\Controllers;

use App\Models\InternalTx;
use Illuminate\Contracts\View\View;

class InternalTxController extends Controller
{
    public function __construct()
    {
        $this->authorizeResource(InternalTx::class, 'internal');
    }

    public function index(): View
    {
        return view('transactions.internal.index');
    }

    public function show(InternalTx $internal): View
    {
        return view('transactions.internal.show', [
            'transaction' => $internal,
        ]);
    }
}
