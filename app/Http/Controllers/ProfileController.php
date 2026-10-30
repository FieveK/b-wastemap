<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileUpdateRequest;
use App\Models\Penanganan;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class ProfileController extends Controller
{
    /**
     * Display the user's profile form.
     */
    public function edit(Request $request): View
    {
        return view('profile.edit', [
            'user' => $request->user(),
        ]);
    }

    /**
     * Update the user's profile information.
     */
    public function update(ProfileUpdateRequest $request): RedirectResponse
    {
        $request->user()->fill($request->validated());

        if ($request->user()->isDirty('email')) {
            $request->user()->email_verified_at = null;
        }

        $request->user()->save();

        return Redirect::route('profile.edit')->with('status', 'profile-updated');
    }

    /**
     * Delete the user's account.
     */
    public function destroy(Request $request): RedirectResponse
    {
        $request->validateWithBag('userDeletion', [
            'password' => ['required', 'current_password'],
        ]);

        $user = $request->user();

        DB::transaction(function () use ($user) {
            $user = User::query()->lockForUpdate()->findOrFail($user->id);

            if ($user->role === 'admin' && $user->is_active) {
                $activeAdmins = User::query()
                    ->where('role', 'admin')
                    ->where('is_active', true)
                    ->lockForUpdate()
                    ->get(['id']);

                if ($activeAdmins->count() <= 1) {
                    throw ValidationException::withMessages([
                        'password' => 'Setidaknya harus ada satu akun admin aktif.',
                    ])->errorBag('userDeletion');
                }
            }

            if (
                $user->role === 'petugas'
                && Penanganan::query()
                    ->where('petugas_id', $user->id)
                    ->whereIn('status', ['ditugaskan', 'dalam_penanganan'])
                    ->exists()
            ) {
                throw ValidationException::withMessages([
                    'password' => 'Selesaikan tugas aktif sebelum menghapus akun petugas.',
                ])->errorBag('userDeletion');
            }

            $user->delete();
        });

        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return Redirect::to('/');
    }
}
