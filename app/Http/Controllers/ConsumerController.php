<?php

namespace App\Http\Controllers;

use App\Http\Requests\{CreateConsumerRequest, PinRequest};
use App\Jobs\UpdateOrderAmount;
use App\Models\Consumer;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;

class ConsumerController extends Controller
{
    public function __construct()
    {
        $this->authorizeResource(Consumer::class);
    }

    /**
     * Display a listing of the resource.
     *
     * @return View
     */
    public function index(): View
    {
        return view('consumers.index');
    }

    public function show(Consumer $consumer): View
    {
        return view('consumers.show', compact('consumer'));
    }

    public function create(): View
    {
        return view('consumers.create');
    }

    public function store(CreateConsumerRequest $request): RedirectResponse
    {
        $consumer = Consumer::create($request->validated());
        $consumer->order()->create(['resource_amount' => $consumer->resource_amount]);

        return to_route('consumers.index')->with('success', __('message.mission_complete'));
    }

    public function edit(Consumer $consumer): View
    {
        return view('consumers.edit', compact('consumer'));
    }

    public function update(CreateConsumerRequest $request, Consumer $consumer): RedirectResponse
    {
        $consumer->update($request->validated());

        if ($consumer->wasChanged('resource_amount')) {
            UpdateOrderAmount::dispatch($consumer->order);
        }

        return to_route('consumers.show', $consumer)->with('success', __('message.mission_complete'));
    }

    public function destroy(PinRequest $request, Consumer $consumer): RedirectResponse
    {
        $consumer->delete();

        return to_route('consumers.index')->with('success', __('message.mission_complete'));
    }
}
