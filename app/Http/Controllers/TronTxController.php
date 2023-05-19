<?php

namespace App\Http\Controllers;

use App\Models\TronTx;
use Illuminate\Contracts\View\View;

class TronTxController extends Controller
{
    public function __construct()
    {
        $this->authorizeResource(TronTx::class, 'tron');
    }

    public function index(): View
    {
        return view('transactions.tron.index');
    }

    public function show(TronTx $tron): View
    {
        return view('transactions.tron.show', [
            'transaction' => $tron,
        ]);
    }
}
