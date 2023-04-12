<?php

namespace App\Http\Controllers;

use App\Http\Requests\CreateConsumerRequest;
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

    /**
     * Show the form for creating a new resource.
     *
     * @return View
     */
    public function create(): View
    {
        return view('consumers.create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param CreateConsumerRequest $request
     * @return RedirectResponse
     */
    public function store(CreateConsumerRequest $request): RedirectResponse
    {
        Consumer::query()->create($request->validated());

        return to_route('consumers.index')->with('success', __('message.mission_complete'));
    }
}
