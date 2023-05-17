<?php

namespace App\Http\Controllers;

use App\Enums\Statuses;
use App\Http\Requests\PinRequest;
use App\Models\{User, Withdrawal};
use Illuminate\Contracts\View\View;
use Illuminate\Http\{RedirectResponse};

class WithdrawalController extends Controller
{
    public function __construct()
    {
        $this->authorizeResource(Withdrawal::class);
    }

    public function index(): View
    {
        return view('withdrawals.index');
    }

    public function show(Withdrawal $withdrawal): View
    {
        $user = User::with('wallet:user_id,address')->find($withdrawal->user_id);
        $walletAddress = $user->wallet?->address;

        return view('withdrawals.show', compact('withdrawal', 'walletAddress'));
    }

    public function accept(PinRequest $request, Withdrawal $withdrawal): RedirectResponse
    {
        $withdrawal->update(['status' => Statuses::completed]);

        return to_route('withdrawals.show', $withdrawal)->with('success', __('message.mission_complete'));
    }

    public function decline(PinRequest $request, Withdrawal $withdrawal): RedirectResponse
    {
        $withdrawal->update(['status' => Statuses::declined]);

        return to_route('withdrawals.show', $withdrawal)->with('success', __('message.mission_complete'));
    }
}
