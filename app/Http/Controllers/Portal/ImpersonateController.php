<?php

namespace App\Http\Controllers\Portal;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ImpersonateController extends Controller
{
    /**
     * Impersonate (Login As) another user.
     */
    public function login(Request $request, User $user)
    {
        if (!\Illuminate\Support\Facades\Gate::allows('impersonate', $user)) {
            abort(403, 'Hanya Super Admin yang dapat menggunakan fitur Login As.');
        }

        // Save original admin user ID in session
        session(['impersonator_id' => auth()->id()]);

        // Login as the target user
        Auth::login($user);

        // Redirect based on target user role
        $targetRole = strtolower($user->roles->first()?->name ?? '');
        if (in_array($targetRole, ['siswa', 'orang_tua', 'wali', 'parent'])) {
            return redirect()->to('/dashboard')->with('success', "Berhasil masuk sebagai {$user->name}.");
        } else {
            return redirect()->to('/admin')->with('success', "Berhasil masuk sebagai {$user->name}.");
        }
    }

    /**
     * Stop impersonating and return to the original super admin session.
     */
    public function logout()
    {
        if (!session()->has('impersonator_id')) {
            return redirect()->route('dashboard');
        }

        $originalUserId = session('impersonator_id');
        $originalUser = User::find($originalUserId);

        if (!$originalUser) {
            session()->forget('impersonator_id');
            return redirect()->route('login');
        }

        // Log back in as original user
        Auth::login($originalUser);

        // Clear session
        session()->forget('impersonator_id');

        return redirect('/admin')->with('success', 'Kembali ke sesi Super Admin.');
    }
}
