<?php

namespace App\Http\Controllers;

use App\Http\Requests\CreateOrderRequest;
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

    /**
     * Show the form for creating a new resource.
     *
     * @return View
     */
    public function create(): View
    {
        $consumers = Consumer::all(['id', 'name']);

        return view('orders.create', compact('consumers'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param CreateOrderRequest $request
     * @return RedirectResponse
     */
    public function store(CreateOrderRequest $request): RedirectResponse
    {
        Order::query()->create($request->validated());

        return to_route('orders.index')->with('success', __('message.mission_complete'));
    }
}
