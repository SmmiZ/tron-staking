<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use Illuminate\Contracts\View\View;

class TransactionController extends Controller
{
    public function __construct()
    {
        $this->authorizeResource(Transaction::class);
    }

    public function index(): View
    {
        return view('transactions.index');
    }

    public function show(Transaction $transaction): View
    {
        return view('transactions.show', compact('transaction'));
    }
}
