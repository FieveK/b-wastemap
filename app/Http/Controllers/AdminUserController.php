<?php

namespace App\Http\Controllers;

use App\Models\Penanganan;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class AdminUserController extends Controller
{
    private const ROLES = ['admin', 'petugas', 'masyarakat'];

    public function index(Request $request): View
    {
        $search = $request->query('search');
        $search = is_string($search) ? mb_substr(trim($search), 0, 100) : '';
        $editId = $request->query('edit');

        $users = User::query()
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($query) use ($search) {
                    $query->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%");
                });
            })
            ->when(in_array($request->query('role'), self::ROLES, true), fn ($query) => $query->where('role', $request->query('role')))
            ->when(in_array($request->query('status'), ['aktif', 'nonaktif'], true), fn ($query) => $query->where('is_active', $request->query('status') === 'aktif'))
            ->orderBy('name')
            ->paginate(10)
            ->withQueryString();

        $editingUser = is_string($editId) && ctype_digit($editId)
            ? User::findOrFail((int) $editId)
            : null;

        return view('admin.users.index', compact('users', 'editingUser', 'search'));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'role' => ['required', 'in:admin,petugas,masyarakat'],
            'is_active' => ['required', 'boolean'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        $data['is_active'] = $request->boolean('is_active');
        User::create($data);

        return redirect()->route('admin.users.index')->with('success', 'Akun pengguna berhasil dibuat.');
    }

    public function update(Request $request, User $user): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email,'.$user->id],
            'role' => ['required', 'in:admin,petugas,masyarakat'],
            'is_active' => ['required', 'boolean'],
            'password' => ['nullable', 'string', 'min:8', 'confirmed'],
        ]);

        $data['is_active'] = $request->boolean('is_active');

        if (blank($data['password'] ?? null)) {
            unset($data['password']);
        }

        DB::transaction(function () use ($request, $user, $data) {
            $user = User::query()->lockForUpdate()->findOrFail($user->id);

            if ($user->is($request->user()) && ($data['role'] !== $user->role || ! $data['is_active'])) {
                throw ValidationException::withMessages([
                    'account' => 'Akun admin yang sedang digunakan tidak dapat diturunkan perannya atau dinonaktifkan.',
                ]);
            }

            if ($user->role === 'admin' && $user->is_active && ($data['role'] !== 'admin' || ! $data['is_active'])) {
                $activeAdmins = User::query()
                    ->where('role', 'admin')
                    ->where('is_active', true)
                    ->lockForUpdate()
                    ->get(['id']);

                if ($activeAdmins->count() <= 1) {
                    throw ValidationException::withMessages([
                        'account' => 'Setidaknya harus ada satu akun admin aktif.',
                    ]);
                }
            }

            if (
                $user->role === 'petugas'
                && ($data['role'] !== 'petugas' || ! $data['is_active'])
                && Penanganan::query()
                    ->where('petugas_id', $user->id)
                    ->whereIn('status', ['ditugaskan', 'dalam_penanganan'])
                    ->exists()
            ) {
                throw ValidationException::withMessages([
                    'account' => 'Selesaikan tugas aktif sebelum menonaktifkan akun petugas atau mengubah perannya.',
                ]);
            }

            $user->update($data);
        });

        return back()->with('success', 'Perubahan akun berhasil disimpan.');
    }
}
