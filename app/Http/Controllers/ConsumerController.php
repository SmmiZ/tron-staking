<?php

namespace App\Http\Controllers;

use App\Http\Requests\{CreateConsumerRequest, DestroyConsumerRequest};
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
        Consumer::query()->create($request->validated());

        return to_route('consumers.index')->with('success', __('message.mission_complete'));
    }

    public function destroy(DestroyConsumerRequest $request, Consumer $consumer): RedirectResponse
    {
        $consumer->delete();

        return to_route('consumers.index')->with('success', __('message.mission_complete'));
    }
}
