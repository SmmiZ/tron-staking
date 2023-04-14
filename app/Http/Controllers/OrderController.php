<?php

namespace App\Http\Controllers;

use App\Http\Requests\{CreateOrderRequest, PinRequest};
use App\Models\{Consumer, Order};
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;

class OrderController extends Controller
{
    public function __construct()
    {
        $this->authorizeResource(Order::class);
    }

    /**
     * Display a listing of the resource.
     *
     * @return View
     */
    public function index(): View
    {
        return view('orders.index');
    }

    public function show(Order $order): View
    {
        return view('orders.show', compact('order'));
    }

    public function create(): View
    {
        $consumers = Consumer::all(['id', 'name']);

        return view('orders.create', compact('consumers'));
    }

    public function store(CreateOrderRequest $request): RedirectResponse
    {
        Order::query()->create($request->validated());

        return to_route('orders.index')->with('success', __('message.mission_complete'));
    }

    public function destroy(PinRequest $request, Order $order): RedirectResponse
    {
        $order->delete();

        return to_route('orders.index')->with('success', __('message.mission_complete'));
    }
}
