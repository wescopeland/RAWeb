<?php

declare(strict_types=1);

namespace App\Platform\Controllers;

use App\Http\Controller;
use App\Models\Emulator;
use App\Platform\Requests\EmulatorRequest;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class EmulatorController extends Controller
{
    protected function resourceName(): string
    {
        return 'emulator';
    }

    public function index(): View
    {
        Gate::authorize('viewAny', $this->resourceClass());

        return view('resource.index')
            ->with('resource', $this->resourceName());
    }

    public function create(): void
    {
        Gate::authorize('create', $this->resourceClass());
    }

    public function store(Request $request): void
    {
        Gate::authorize('create', $this->resourceClass());
    }

    public function show(Emulator $emulator): void
    {
        Gate::authorize('view', $emulator);
    }

    public function edit(Emulator $emulator): View
    {
        Gate::authorize('update', $emulator);

        $emulator->loadMissing([
            'systems' => function ($query) {
                $query->orderBy('name');
            },
            'latestRelease',
        ]);

        $emulator->loadCount(['releases', 'systems']);

        return view('emulator.edit')
            ->with('emulator', $emulator);
    }

    public function update(EmulatorRequest $request, Emulator $emulator): RedirectResponse
    {
        Gate::authorize('update', $emulator);

        $data = $request->validated();
        $data['active'] ??= false;

        $emulator->fill($data)->save();

        return redirect(route('emulator.edit', $emulator))
            ->with('success', $this->resourceActionSuccessMessage('emulator', 'update'));
    }

    public function destroy(Emulator $emulator): void
    {
        Gate::authorize('delete', $emulator);
    }
}
