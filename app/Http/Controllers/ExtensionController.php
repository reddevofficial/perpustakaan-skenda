<?php

namespace App\Http\Controllers;

use App\Models\Extension;
use App\Models\Loan;
use App\Services\ExtensionService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use RuntimeException;

class ExtensionController extends Controller
{
    public function store(Loan $loan, ExtensionService $extensionService): RedirectResponse
    {
        $student = Auth::user()?->student;
        abort_unless($student, 403);

        try {
            $extensionService->request($student, $loan, request('reason'));
        } catch (RuntimeException $exception) {
            return back()->with('error', $exception->getMessage());
        }

        return back()->with('success', 'Permintaan perpanjangan berhasil dikirim.');
    }

    public function approve(Extension $extension, ExtensionService $extensionService): RedirectResponse
    {
        Gate::authorize('manage-loans');
        $extensionService->approve($extension, Auth::id());

        return back()->with('success', 'Perpanjangan disetujui.');
    }

    public function reject(Extension $extension, ExtensionService $extensionService): RedirectResponse
    {
        Gate::authorize('manage-loans');
        $extensionService->reject($extension, Auth::id(), request('reason'));

        return back()->with('success', 'Perpanjangan ditolak.');
    }
}
