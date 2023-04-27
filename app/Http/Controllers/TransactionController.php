<?php

namespace App\Http\Controllers;

use App\Models\TronTx;
use Illuminate\Contracts\View\View;

class TransactionController extends Controller
{
    public function __construct()
    {
        $this->authorizeResource(TronTx::class);
    }

    public function index(): View
    {
        return view('transactions.index');
    }

    public function show(TronTx $transaction): View
    {
        return view('transactions.show', compact('transaction'));
    }
}
